<?php
namespace VcsCommon;

use VcsCommon\exception\CommonException;
use yii\base\Object;

/**
 * Represents base branch model
 */
abstract class BaseBranch extends Object
{
    /**
     * @var BaseRepository
     */
    protected $repository;

    /**
     * @var string branch identifier
     */
    protected $id;

    /**
     * @var string head commit identifier
     */
    protected $head;

    /**
     * @var boolean true if branch is currently selected
     */
    protected $isCurrent;

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
        if (!$this->head || !is_string($this->head)) {
            throw new CommonException("Head property required");
        }
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
     * Sets id only by constructor
     *
     * @param string $value
     */
    public function setId($value)
    {
        if (is_string($value) && is_null($this->id)) {
            $this->id = $value;
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
     * Sets head only by constructor
     *
     * @param string $value
     */
    public function setHead($value)
    {
        if (is_scalar($value) && is_null($this->head)) {
            $this->head = (string) $value;
        }
    }

    /**
     * Returns head id
     *
     * @return string
     */
    public function getHead()
    {
        return $this->head;
    }

    /**
     * Sets current flag only by constructor
     *
     * @param boolean $value
     */
    public function setIsCurrent($value)
    {
        if (is_scalar($value) && is_null($this->isCurrent)) {
            $this->isCurrent = (boolean) $value;
        }
    }

    /**
     * Returns isCurrent flag
     *
     * @return boolean
     */
    public function getIsCurrent()
    {
        return $this->isCurrent;
    }

    /**
     * Returns head commit instance
     *
     * @return BaseCommit
     * @throws CommonException
     */
    abstract public function getHeadCommit();
}