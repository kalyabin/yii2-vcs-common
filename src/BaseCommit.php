<?php
namespace VcsCommon;

use DateTime;
use VcsCommon\exception\CommonException;
use yii\base\Object;

/**
 * Represents base commit model
 */
abstract class BaseCommit extends Object
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
     * @var File[] Changed files list with statuses codes
     */
    protected $changedFiles = [];

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
        if (!$this->id || !is_string($this->id)) {
            throw new CommonException("Id property required");
        }
        if (!$this->contributorName) {
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
     * Get a raw file at previous revision.
     *
     * Returns file contents.
     *
     * @param string $filePath path to file to view
     * @throws CommonException
     */
    abstract public function getPreviousRawFile($filePath);

    /**
     * Push changed file to list.
     *
     * For input array structure see changedFiles documentation.
     *
     * @see changedFiles
     * @param File $item
     */
    public function appendChangedFile(File $item)
    {
        // validate item and put it to stack
        $this->changedFiles[$item->getPath()] = $item;
        ksort($this->changedFiles);
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
        foreach ($this->changedFiles as $item) {
            /* @var $path File */
            if (ltrim($item->getPathname(), DIRECTORY_SEPARATOR) === ltrim($filePath, DIRECTORY_SEPARATOR)) {
                return $item->getStatus();
            }
        }

        return null;
    }

    /**
     * Set change files by commit.
     *
     * For input array structure see changedFiles documentation.
     *
     * @see changedFiles
     * @param array $list list of changed files
     */
    public function setChangedFiles(array $list)
    {
        $this->changedFiles = [];
        foreach ($list as $item) {
            // validate file
            if (is_array($item)) {
                $this->appendChangedFile($item);
            }
        }
    }

    /**
     * @return array changed files by commit
     */
    public function getChangedFiles()
    {
        return array_values($this->changedFiles);
    }
}
