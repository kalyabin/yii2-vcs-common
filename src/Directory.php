<?php
namespace VcsCommon;

/**
 * Implements directory operations
 *
 * Directory may be exists now, or sometime removed.
 * This object implements everybody operations of stored directories in repository.
 */
class Directory extends File
{
    /**
     * Returns true if directory exists now.
     * If it returns false - directory exists sometime at repository.
     *
     * @return boolean
     */
    public function exists()
    {
        return is_dir($this->path);
    }
}
