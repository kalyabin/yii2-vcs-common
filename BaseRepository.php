<?php
namespace vcsCommon;

use DirectoryIterator;
use vcsCommon\exception\RepositoryException;
use yii\base\Object;
use yii\helpers\FileHelper;

/**
 * Abstract repository class
 * Provides access control to project repository
 */
abstract class BaseRepository extends Object
{
    /**
     * @var BaseWrapper common VCS interface
     */
    protected $wrapper;

    /**
     * @var string path to project
     */
    protected $projectPath;

    /**
     * @var string path to .git, .hg, etc
     */
    protected $repositoryPath;

    /**
     * Get repository from directory.
     * Throws RepositoryException if repository not found at dir.
     *
     * @param string $dir project path (not a path to .git or .hg!)
     * @param BaseWrapper $wrapper
     * @throws RepositoryException
     */
    public function __construct($dir, BaseWrapper $wrapper)
    {
        $projectPath = FileHelper::normalizePath(realpath($dir));
        $repositoryPath = FileHelper::normalizePath($projectPath . '/' . $wrapper->getRepositoryPathName());
        if (!is_dir($repositoryPath)) {
            throw new RepositoryException('Repository not found at ' . $dir);
        }
        $this->projectPath = $projectPath;
        $this->repositoryPath = $repositoryPath;
        $this->wrapper = $wrapper;
        $this->checkStatus();
        parent::__construct([]);
    }

    /**
     * Returns VCS common interface
     *
     * @return BaseWrapper
     */
    public function getWrapper()
    {
        return $this->wrapper;
    }

    /**
     * Returns project path
     *
     * @return string
     */
    public function getProjectPath()
    {
        return $this->projectPath;
    }

    /**
     * Returns repository path
     *
     * @return string
     */
    public function getRepositoryPath()
    {
        return $this->repositoryPath;
    }

    /**
     * Check repository status and returns it.
     *
     * @return string
     * @throws RepositoryException
     */
    abstract public function checkStatus();

    /**
     * Returns repository files list.
     * Param $subDir must be a subdirectory of project repository.
     *
     * @param string $subDir
     * @return File[]
     * @throws RepositoryException
     */
    public function getFilesList($subDir = null)
    {
        $list = [];

        $dir = FileHelper::normalizePath(realpath($this->projectPath . '/' . $subDir));

        if (!is_dir($dir) || $dir == $this->repositoryPath) {
            throw new RepositoryException("Path $dir is not a directory");
        }

        $iterator = new DirectoryIterator($dir);
        foreach ($iterator as $path) {
            try {
                $file = null;
                if (
                    ($path->isDir() && !$path->isDot() && $path->getFilename() != $this->wrapper->getRepositoryPathName()) ||
                    ($path->isDot() && $path->getFilename() != '.')
                ) {
                    $file = new Directory($path, $this);
                }
                else if ($path->isFile()) {
                    $file = new File($path, $this);
                }
                else if ($path->isLink()) {
                    $file = new FileLink($path, $this);
                }
                if ($file instanceof File) {
                    $list[] = $file;
                }
            }
            catch (RepositoryException $ex) { }
        }

        return $list;
    }
}