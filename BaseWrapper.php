<?php
namespace VcsCommon;

use VcsCommon\exception\CommonException;
use yii\base\Object;

/**
 * Abstract class provides access control to basic console commands
 * of version control system.
 *
 * Basicly type a cmd property and basics methods like checkVersion, getStatus etc.
 */
abstract class BaseWrapper extends Object
{
    /**
     * @var string path to console VCS (git, hg, etc.) command
     */
    protected $cmd;

    /**
     * @var string VCS version (git --version, hg version, etc.)
     */
    protected $version;

    /**
     * Sets cmd property and checks VCS version
     *
     * @param string $cmd
     */
    public function setCmd($cmd)
    {
        $this->cmd = $cmd;
        $this->checkVersion();
    }

    /**
     * Returns cmd property
     *
     * @return string
     */
    public function getCmd()
    {
        return $this->cmd;
    }

    /**
     * Returns version
     *
     * @return string
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * Build console command.
     * Params may be array, or string.
     *
     * If it's array like this:
     * ['log', '--skip' => 10, '-L', 10]
     *
     * then result be: 'git log --skip=10 -L 10'.
     *
     * @param string|array $params console command params
     * @return string
     */
    public function buildCommand($params)
    {
        $ret = '';

        if (is_scalar($params)) {
            $ret = $params;
        }
        else if (is_array($params)) {
            $ret = [];
            foreach ($params as $k => $v) {
                if (is_string($k) && trim($k) && is_scalar($v)) {
                    $ret[] = "$k=$v";
                }
                else if (is_scalar($v)) {
                    $ret[] = $v;
                }
            }
            $ret = implode(' ', $ret);
        }

        return !empty($ret) ? $this->cmd . ' ' . $ret : $this->cmd;
    }

    /**
     * Execute VCS command with params.
     *
     * @param string|array $params command prefix, see buildCommand method for details.
     * @param string $dir directory in which the command is executed
     * @param boolean $getArray returns execution result as array if true, or string if false
     * @return string|array
     * @throws CommonException
     */
    public function execute($params, $dir = null, $getArray = false)
    {
        $currentDirectory = getcwd();
        $result = [];
        $exitCode = 0;
        $cmd = $this->buildCommand($params);
        if ($dir) {
            chdir($dir);
        }
        exec($cmd, $result, $exitCode);
        if ($exitCode != 0) {
            chdir($currentDirectory);
            throw new CommonException('Command ' . $cmd . ' ended with ' . $exitCode . ' status code', $exitCode);
        }
        chdir($currentDirectory);
        return $getArray ? $result : implode(PHP_EOL, $result);
    }

    /**
     * Checks VCS version and set it to version property.
     *
     * @throws CommonException
     */
    abstract public function checkVersion();

    /**
     * Returns repository path name like .git, .hg, etc.
     *
     * @return string
     */
    abstract public function getRepositoryPathName();

    /**
     * Create repository instance by provided directory.
     * Directory must be a path of project (not a .git path).
     *
     * @param string $dir project directory
     * @return BaseRepository
     * @throws CommonException
     */
    abstract public function getRepository($dir);
}