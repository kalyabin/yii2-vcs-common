<?php
namespace VcsCommon;

use yii\base\BaseObject;

/**
 * Diff object. Parse command line results to public variables.
 */
abstract class BaseDiff extends BaseObject
{
    /**
     * Path to nobody file (for checks new or removed files)
     */
    const NULL_PATH = '/dev/null';

    /**
     * @var string raw diff head description without file lines
     */
    protected $description;

    /**
     * @var boolean true if diff is binary
     */
    protected $isBinary = false;

    /**
     * Changed lines.
     *
     * Array like this:
     * ```php
     * array(
     *     '@@ -1,4 +1,4 @@' => array(
     *             'beginA' => 1,
     *             'beginB' => 2,
     *             'cntA' => 4,
     *             'cntB' => 4,
     *             'lines' => array(
     *                '-test word 1 this temporary row first',
     *                '+test word 1 this temporary row first1',
     *                ' test word 2 this temporary row second',
     *                ' test word 3 thos temporary row third',
     *                ' this row will be constant',
     *             ),
     *     ),
     *     // any changes at file
     * )
     * ```
     * @var string[]
     */
    protected $lines = array();

    /**
     * @var string relative path to previos file version
     */
    protected $previousFilePath;

    /**
     * @var string relative path to new file version
     */
    protected $newFilePath;

    /**
     * Create object using console result rows.
     *
     * @param string[] $consoleResult
     * @param array $config
     */
    public function __construct($consoleResult, $config = array())
    {
        parent::__construct($config);
        $this->initialize($consoleResult);
    }

    /**
     * Sets object properties using $rows param from console command.
     *
     * @param string[] $rows
     */
    abstract protected function initialize($rows);

    /**
     * Returns true if file was removed at commit.
     *
     * @return boolean
     */
    public function fileRemoved()
    {
        return $this->newFilePath = self::NULL_PATH;
    }

    /**
     * Returns true if file was created at commit.
     *
     * @return boolean
     */
    public function fileIsNew()
    {
        return $this->previousFilePath = self::NULL_PATH;
    }

    /**
     * Returns description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Returns is binary flag
     *
     * @return boolean
     */
    public function getIsBinary()
    {
        return $this->isBinary;
    }

    /**
     * Returns new file path
     *
     * @return string
     */
    public function getNewFilePath()
    {
        return $this->newFilePath;
    }

    /**
     * Returns previos file path
     *
     * @return string
     */
    public function getPreviousFilePath()
    {
        return $this->previousFilePath;
    }

    /**
     * Returns changed file lines
     *
     * @return array
     */
    public function getLines()
    {
        return $this->lines;
    }
}
