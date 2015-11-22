<?php
namespace VcsCommon;

use yii\base\Object;

/**
 * Diff object. Parse command line results to public variables.
 */
abstract class BaseDiff extends Object
{
    /**
     * Path to nobody file (for checks new or removed files)
     */
    const NULL_PATH = '/dev/null';

    /**
     * @var string raw diff head description without file lines
     */
    public $description;

    /**
     * @var boolean true if diff is binary
     */
    public $isBinary = false;

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
    public $lines = array();

    /**
     * @var string relative path to previos file version
     */
    public $previousFilePath;

    /**
     * @var string relative path to new file version
     */
    public $newFilePath;

    /**
     * Sets public properties from command line using string variable.
     *
     * @param string[] $str
     */
    abstract public function setResults($str);

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
}