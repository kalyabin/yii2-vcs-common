<?php
namespace VcsCommon;

use yii\base\Object;

/**
 * Class represents graph history log.
 * Contains:
 * - levels - count of nested commits;
 * - commits - all commits in a history.
 */
class Graph extends Object
{
    /**
     * @var integer nested level
     */
    protected $levels = 0;

    /**
     * @var BaseCommit[] commits array
     */
    protected $commits = [];

    /**
     * Push new commit to stack and check him level.
     * Used static var to check head levels commits.
     *
     * @staticvar array $headLevels
     * @param BaseCommit $commit
     */
    public function pushCommit(BaseCommit $commit)
    {
        $this->commits[$commit->getId()] = $commit;
        $this->levels = max($commit->graphLevel, $this->levels);
    }

    /**
     * @return integer maximum level num
     */
    public function getLevels()
    {
        return $this->levels;
    }

    /**
     * @return BaseCommit[] get stack commits
     */
    public function getCommits()
    {
        return array_values($this->commits);
    }
}
