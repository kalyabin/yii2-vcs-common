<?php
namespace VcsCommon;

use VcsCommon\exception\CommonException;
use yii\base\Object;
use yii\helpers\FileHelper;
use yii\helpers\StringHelper;

/**
 * Implements file operations.
 *
 * File may be exists now, or sometime removed.
 * This object implements everybody operations of stored files in repository.
 */
class File extends Object
{
    /**
     * @var string file name
     */
    protected $name;

    /**
     * @var string file path
     */
    protected $path;

    /**
     * @var BaseRepository
     */
    protected $repository;

    /**
     * Constructor
     *
     * @param string $pathName
     * @param BaseRepository $repository
     * @throws CommonException
     */
    public function __construct($pathName, BaseRepository $repository)
    {
        $this->name = basename($pathName);
        $this->path = FileHelper::normalizePath($pathName);
        $this->repository = $repository;
        if (!StringHelper::startsWith($this->path, $repository->getProjectPath())) {
            throw new CommonException("Path {$this->path} outband of repository");
        }
        parent::__construct([]);
    }

    /**
     * Returns file path
     *
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * Returns directory path
     *
     * @return string
     */
    public function getDirectory()
    {
        return dirname($this->path);
    }

    /**
     * Returns file base name
     *
     * @return string
     */
    public function getFileName()
    {
        return $this->name;
    }

    /**
     * Return path name without repository path
     *
     * @return string
     */
    public function getPathname()
    {
        return ltrim(mb_substr($this->path, mb_strlen($this->repository->getProjectPath())), DIRECTORY_SEPARATOR);
    }

    /**
     * Returns true if file exists now.
     * If it returns false - file exists sometime at repository.
     *
     * @return boolean
     */
    public function exists()
    {
        return is_file($this->path);
    }
}
