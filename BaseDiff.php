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
     * Array new file lines.
     * Array key is diff identifier like '@@ -18,4 +18,14 @@', and values is
     * array of changed lines, where key is line number.
     *
     * For example:
     * ```php
     * array(
     *     '@@ -18,3 +18,4 @@' => array(
     *         18 => 'old line 1',
     *         19 => 'old line 2',
     *         20 => 'old line 3',
     *         21 => 'this new file line',
     *         // if null, line do not exists at new file version
     *     ),
     *     // etc
     * )
     * ```
     * @var string[]
     */
    public $newLines = array();

    /**
     * Array old lines.
     * Array key is diff identifier like '@@ -8,4 +8,14 @@', and values is
     * array of changed lines, where key is line number.
     *
     * For example:
     * ```php
     * array(
     *     '@@ -18,3 +18,4 @@' => array(
     *         18 => 'old line 1',
     *         19 => 'old line 2',
     *         20 => 'old line 3',
     *         21 => null, // line do not exists at old file version
     *         // if null, line do not exists at old file version
     *     ),
     *     // etc
     * )
     * ```
     * @var string[]
     */
    public $previousLines = array();

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