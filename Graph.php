<?php
namespace VcsCommon;

use yii\base\Object;

/**
 * Class represents graph history log.
 *
 * Contains two params:
 * - pieces - graph pieces like: /, \, |, space or commit.
 * - commit - if type is commit, contains BaseCommit object, or null if else.
 */
class Graph extends Object
{
    /**
     * Graphic pieces types
     */
    const RIGHT = '/';
    const LEFT = '\\';
    const DIRECT = '|';
    const SPACE = ' ';
    const COMMIT = '*';

    /**
     * @var string[] graph item pieces
     */
    protected $graphPieces = [];

    /**
     * @var BaseCommit|null commit item, or null
     */
    protected $commit;

    /**
     * Piece setter
     *
     * @param string $val
     */
    public function appendPiece($val)
    {
        if (in_array($val, [self::RIGHT, self::LEFT, self::DIRECT, self::COMMIT])) {
            $this->graphPieces[] = $val;
        }
        else {
            $this->graphPieces[] = self::SPACE;
        }
    }

    /**
     * Returns graph pieces
     *
     * @return string[]
     */
    public function getType()
    {
        return !empty($this->graphPieces) ? $this->graphPieces : [self::DIRECT];
    }

    /**
     * Commit setter
     *
     * @param BaseCommit $val
     */
    public function setCommit($val)
    {
        if ($val instanceof BaseCommit) {
            $this->commit = $val;
        }
    }

    /**
     * Returns commit property
     *
     * @return BaseCommit|null
     */
    public function getCommit()
    {
        return $this->commit;
    }

    /**
     * Returns true if pieces has commit property
     *
     * @return boolean
     */
    public function hasCommitPiece()
    {
        return in_array(self::COMMIT, $this->graphPieces);
    }
}