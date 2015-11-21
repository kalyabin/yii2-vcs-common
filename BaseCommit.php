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
     * it's required more memory usage.
     *
     * @param string $file path to file diff
     * @return BaseDiff[] all files diffs or specific file diff
     * @throws CommonException
     */
    abstract public function getDiff($file = null);
}