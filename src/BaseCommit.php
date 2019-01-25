<?php
namespace VcsCommon;

use DateTime;
use VcsCommon\exception\CommonException;
use yii\base\BaseObject;

/**
 * Represents base commit model
 */
abstract class BaseCommit extends BaseObject
{
    /**
     * @var BaseRepository
     */
    protected $repository;

    /**
     * @var string commit identifier
     */
    protected $id;

    /**
     * @var DateTime commit date
     */
    protected $date;

    /**
     * @var string commit message
     */
    public $message;

    /**
     * @var string contributor name
     */
    public $contributorName;

    /**
     * @var string contributor e-mail
     */
    public $contributorEmail;

    /**
     * @var integer nested level to draw graph history
     */
    public $graphLevel;

    /**
     * @var string[] parents commits identifiers
     */
    protected $parentsId = [];

    /**
     * Constructor
     *
     * @param BaseRepository $repository
     * @param array $config public properties set
     */
    public function __construct(BaseRepository $repository, $config)
    {
        $this->repository = $repository;
        parent::__construct($config);
    }

    /**
     * Checks required properties after construct
     *
     * @throws CommonException
     */
    public function init()
    {
        $this->id = is_scalar($this->id) ? (string) $this->id : null;
        if (trim($this->id) == '') {
            throw new CommonException("Id property required");
        }
        if (empty($this->contributorName)) {
            throw new CommonException("Contributor name required");
        }
        if (!$this->date || !($this->date instanceof DateTime)) {
            throw new CommonException("Date is required");
        }
    }

    /**
     * Sets id only by constructor
     *
     * @param string $value
     */
    public function setId($value)
    {
        if (is_scalar($value) && is_null($this->id)) {
            $this->id = (string) $value;
        }
    }

    /**
     * Returns id
     *
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set parents commits ids.
     * If sent as array - push as it, if sent as string - explode by space and push.
     *
     * @param array|string $value
     */
    public function setParentsId($value)
    {
        $this->parentsId = [];

        if (is_array($value)) {
            foreach ($value as $id) {
                if (is_string($id)) {
                    $this->parentsId[] = $id;
                }
            }
        }
        else if (is_string($value)) {
            $this->parentsId = explode(' ', $value);
        }
    }

    /**
     * @return string[] parents commits ids
     */
    public function getParentsId()
    {
        return $this->parentsId;
    }

    /**
     * Returns repository instance
     *
     * @return BaseRepository
     */
    public function getRepository()
    {
        return $this->repository;
    }

    /**
     * Parse date by specific string format
     *
     * @param string $value
     * @return DateTime
     */
    abstract protected function parseDateInternal($value);

    /**
     * Sets date
     *
     * @param mixed $value DateTime instance, timestamp or string
     */
    public function setDate($value)
    {
        if (is_integer($value) || is_float($value)) {
            $this->date = new DateTime($value);
        }
        else if ($value instanceof DateTime) {
            $this->date = $value;
        }
        else if (is_string($value)) {
            $this->date = $this->parseDateInternal($value);
        }
    }

    /**
     * Returns DateTime instance
     *
     * @return DateTime
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Fill commit diff and compare it with previous commit.
     *
     * Returns BaseDiff array.
     *
     * I recommend to use $file parameter to parse only one file diffs, because
     * it's required more memory usage without this parameter.
     *
     * @param string $file path to file diff
     * @return BaseDiff[] all files diffs or specific file diff
     * @throws CommonException
     */
    abstract public function getDiff($file = null);

    /**
     * Get a raw file at this revision.
     *
     * Returns file contents.
     *
     * @param string $filePath path to file to view
     * @throws CommonException
     */
    abstract public function getRawFile($filePath);

    /**
     * Get binary file for specified path and commit identifier.
     *
     * To handle binary data use callback-function $streamHandler, for example:
     *
     * ```php
     * header('Content-type: image/png');
     *
     * $commit->getRawBinaryFile('/path/to/png', function($data) {
     *  echo $data;
     *  flush();
     * });
     * ```
     *
     * @param string $filePath Relative path to binary file
     * @param callable $streamHandler Callback-functin to handle binary data
     */
    abstract public function getRawBinaryFile($filePath, $streamHandler);

    /**
     * Get a raw file at previous revision.
     *
     * Returns file contents.
     *
     * @param string $filePath path to file to view
     * @throws CommonException
     */
    abstract public function getPreviousRawFile($filePath);

    /**
     * Retrieve changed files by console command.
     *
     * Returns associative array: key is a relative file path, value is a file status
     *
     * @return array
     */
    abstract protected function getChangedFilesInternal();

    /**
     * Retrieve file object by relative file path
     *
     * @param string $filePath Relative file path
     *
     * @return File
     */
    public function getFileByPath($filePath)
    {
        $normalizePath = ltrim($filePath, DIRECTORY_SEPARATOR);
        foreach ($this->getChangedFilesInternal() as $filePath => $status) {
            if ($normalizePath == $filePath) {
                return new File($this->repository->getProjectPath() . DIRECTORY_SEPARATOR . $filePath, $this->repository, $status);
            }
        }
        return null;
    }

    /**
     * Returns file status at this commit.
     *
     * If file was changed - returns string status, if else - returns null.
     *
     * @param string $filePath Relative file path
     * @return string|null
     */
    public function getFileStatus($filePath)
    {
        $file = $this->getFileByPath($filePath);
        return $file instanceof File ? $file->getStatus() : null;
    }

    /**
     * Retrieve array of files objects
     *
     * @return File[]
     */
    public function getChangedFiles()
    {
        foreach ($this->getChangedFilesInternal() as $filePath => $status) {
            $file = $this->getFileByPath($filePath);
            yield $file;
        }
    }
}
