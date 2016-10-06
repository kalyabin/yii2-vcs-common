<?php
namespace VcsCommon;

use DirectoryIterator;
use VcsCommon\exception\CommonException;
use yii\base\Object;
use yii\helpers\FileHelper;

/**
 * Abstract repository class
 * Provides access control to project repository
 */
abstract class BaseRepository extends Object
{
    /**
     * Defines a commit diff command
     */
    const DIFF_COMMIT = 'commit';

    /**
     * Defines a compare diff command
     */
    const DIFF_COMPARE = 'compare';

    /**
     * Defines a path diff command
     */
    const DIFF_PATH = 'path';

    /**
     * Defines a full repository diff command
     */
    const DIFF_REPOSITORY = 'repository';

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
     * @throws CommonException
     */
    public function __construct($dir, BaseWrapper $wrapper)
    {
        $projectPath = FileHelper::normalizePath(realpath($dir));
        $repositoryPath = FileHelper::normalizePath($projectPath . '/' . $wrapper->getRepositoryPathName());
        if (!is_dir($repositoryPath)) {
            throw new CommonException('Repository not found at ' . $dir);
        }
        $this->projectPath = $projectPath;
        $this->repositoryPath = $repositoryPath;
        $this->wrapper = $wrapper;
        $this->checkRepository();
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
     * Returns true if repository exists in project path.
     *
     * @return boolean
     * @throws CommonException
     */
    abstract protected function checkRepository();

    /**
     * Check repository status and returns it.
     *
     * @return string
     * @throws CommonException
     */
    abstract public function checkStatus();

    /**
     * Returns repository files list.
     * Param $subDir must be a subdirectory of project repository.
     *
     * @param string $subDir
     * @return File[]
     * @throws CommonException
     */
    public function getFilesList($subDir = null)
    {
        $list = [];

        $dir = FileHelper::normalizePath(realpath($this->projectPath . '/' . $subDir));

        if (!is_dir($dir) || $dir == $this->repositoryPath) {
            throw new CommonException("Path $dir is not a directory");
        }

        $iterator = new DirectoryIterator($dir);
        foreach ($iterator as $i) {
            try {
                $file = null;
                if ($i->isDir() && !$i->isDot() && $i->getFilename() != $this->wrapper->getRepositoryPathName()) {
                    $file = new Directory($i->getPathname(), $this);
                }
                else if ($i->isFile()) {
                    $file = new File($i->getPathname(), $this);
                }
                else if ($i->isLink()) {
                    $file = new FileLink($i->getPathname(), $this);
                }
                if ($file instanceof File) {
                    $list[] = $file;
                }
            }
            catch (CommonException $ex) {
                // do not stop then one file fails
            }
        }

        return $list;
    }

    /**
     * Returns repository branches models.
     *
     * @return BaseBranch[]
     * @throws CommonException
     */
    abstract public function getBranches();

    /**
     * Returns commit object by commit id.
     *
     * @return BaseCommit
     * @throws CommonException
     */
    abstract public function getCommit($id);

    /**
     * Execute diff command by specific params.
     * Can receive everybody params for command line like this:
     *
     * ```php
     * $wrapper = new GitWrapper();
     * $repo = $wrapper->getRepository('/path/to/repository');
     *
     * // get commit diff:
     * print_r($repo->getDiff('commit', '<commit_sha1>'));
     *
     * // get commit compare
     * print_r($repo->getDiff('compare', '<commit_sha1_first_commit>', '<commit_sha1_last_commit>');
     *
     * // get file diff
     * print_r($repo->getDiff('path', '/path/to/file');
     *
     * // get full repo diff
     * print_r($repo->getDiff('repository');
     * ```
     *
     * Returns array: each reults row at new element.
     *
     * @return string[]
     * @throw CommonException
     */
    abstract public function getDiff();

    /**
     * Returns repository history
     *
     * @param integer $limit commits max count
     * @param integer $skip skip count commits
     * @param string $path Get history of specified path (null if get root path history)
     * @param string|null $branch Branch from wich to take a history (by default - null, all branches)
     *
     * @return BaseCommit[] array of commits history with leader last commits
     * @throws CommonException
     */
    abstract public function getHistory($limit, $skip, $path = null, $branch = null);

    /**
     * Returns repository graph history.
     * This is wrapper for getHistory, but sets graphLevel variable to commits.
     *
     * @param integer $limit commits max count
     * @param integer $skip skip count commits
     * @param string $path Get history of specified path (null if get root path history)
     * @param string|null $branch Branch from wich to take a history (by default - null, all branches)
     *
     * @return Graph commits array stack with filled graphLevel variable
     * @throws CommonException
     */
    abstract public function getGraphHistory($limit, $skip, $path = null, $branch = null);

    /**
     * Returns true if path is in repository tracking.
     *
     * @param string $filePath Relative file path
     *
     * @return boolean
     */
    abstract public function pathIsNotIgnored($filePath);
}
