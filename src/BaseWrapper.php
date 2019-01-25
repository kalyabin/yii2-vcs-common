<?php
namespace VcsCommon;

use Yii;
use VcsCommon\exception\CommonException;
use yii\base\BaseObject;

/**
 * Abstract class provides access control to basic console commands
 * of version control system.
 *
 * Basicly type a cmd property and basics methods like checkVersion, getStatus etc.
 */
abstract class BaseWrapper extends BaseObject
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
     * Debug handler to write or process log messages.
     *
     * Like this:
     *
     * ```php
     * function($message) {
     *     fwrite(STDOUT, date('Y-m-d H:i:s') . ': ' . $message . "\n");
     * }
     * ```
     *
     * If it is not defined all debug messages will be written to VCS_DEBUG_FILE.
     *
     * @var callable
     */
    public $debugHandler;

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
     * Returns current application charset
     *
     * @return string
     */
    protected function getCurrentCharset()
    {
        $charset = mb_internal_encoding();

        if (isset(Yii::$app)) {
            $charset = Yii::$app->charset;
        }

        return $charset;
    }

    /**
     * Execute VCS command with params and get binary result.
     *
     * To handle result use callback-function $streamHandler.
     *
     * For example:
     *
     * ```php
     * header('Content-type: image/png');
     *
     * $gitWrapper->executeBinary(function($streamResult) {
     *  echo $streamResult;
     * }, ['show', '<sha1>:</path/to/png>'], '/path/to/git/repository');
     * ```
     *
     * @param callable $streamHandler Callback-function to handle binary result
     * @param string|array $params command prefix, see buildCommand method for details.
     * @param string $dir directory in which the command is executed
     * @param boolean $ignoreErrors skip non-null exit status
     * @throws CommonException
     */
    public function executeBinary($streamHandler, $params, $dir = null, $ignoreErrors = false)
    {
        $currentDirectory = getcwd();

        if ($dir) {
            $this->debug("* Change directory to:\n\t$dir");
            chdir($dir);
        }

        $cmd = $this->buildCommand($params);
        $this->debug("* Execute binary command (" . ($ignoreErrors ? "ignore errors" : "do not ignore errors") . "):\n\t$cmd");
        $res = popen($cmd, 'rb');
        while (!feof($res)) {
            call_user_func($streamHandler, fread($res, 8192));
        }
        $status = pclose($res);
        if ($status != 0 && !$ignoreErrors) {
            $this->debug("* Non-zero status code: $status");
            throw new CommonException('Command ' . $cmd . ' ended with ' . $status . ' status code', $status);
        }

        chdir($currentDirectory);
    }

    /**
     * Execute VCS command with params.
     *
     * @param string|array $params command prefix, see buildCommand method for details.
     * @param string $dir directory in which the command is executed
     * @param boolean $getArray returns execution result as array if true, or string if false
     * @param boolean $ignoreErrors skip non-null exit status
     *
     * @return string|array
     * @throws CommonException
     */
    public function execute($params, $dir = null, $getArray = false, $ignoreErrors = false)
    {
        $currentCharset = $this->getCurrentCharset();
        $currentDirectory = getcwd();
        $result = $getArray ? [] : '';
        $cmd = $this->buildCommand($params);
        if ($dir) {
            $this->debug("* Change directory to:\n\t$dir");
            chdir($dir);
        }
        $this->debug("* Execute command (" . ($ignoreErrors ? "ignore errors" : "do not ignore errors") . "):\n\t$cmd");
        $res = popen($cmd, 'r');
        while (!feof($res)) {
            $row = fgets($res);
            $row = preg_replace('#(.*)\n$#i', '$1', $row);
            if (feof($res) && !trim($row)) {
                // empty ending line
                break;
            }
            // convert to internal encoding
            $charset = mb_detect_encoding($row);
            if (trim($charset) && $charset != $currentCharset) {
                $row = mb_convert_encoding($row, $currentCharset, $charset);
            }
            if ($getArray) {
                $result[] = $row;
            }
            elseif (!empty($result)) {
                $result .= PHP_EOL . $row;
            }
            else {
                $result .= $row;
            }
        }
        $status = pclose($res);
        $debugString = is_array($result) ? implode(PHP_EOL, $result) : $result;
        $this->debug("* Result is: \n$debugString");
        if ($status != 0 && !$ignoreErrors) {
            $this->debug("* Non-zero status code: $status");
            throw new CommonException('Command ' . $cmd . ' ended with ' . $status . ' status code', $status);
        }
        $this->debug("* Change directory to:\n\t$currentDirectory");
        chdir($currentDirectory);
        return $result;
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

    /**
     * Write debug message or other actions with log messages.
     *
     * Debug handler will run only if VCS_DEBUG defined.
     *
     * Message will be written to VCS_DEBUG_FILE if it's defined, and
     * will be sent to debugHandler if it's defined.
     *
     * @param string $message Debug message
     */
    protected function debug($message)
    {
        if (defined('VCS_DEBUG') && VCS_DEBUG === true && !is_callable($this->debugHandler)) {
            $this->debugHandler = function($msg) {
                $debugFile = defined('VCS_DEBUG_FILE') ? VCS_DEBUG_FILE : __DIR__ . '/debug.log';
                file_put_contents($debugFile, $msg . "\n", FILE_APPEND);
            };
        }
        // call debug user function
        if (is_callable($this->debugHandler)) {
            call_user_func($this->debugHandler, $message);
        }
    }
}
