<?php
namespace VcsCommon;

use VcsCommon\File;

/**
 * Implements link operations
 *
 * Link may be exists now, or sometime removed.
 * This object implements everybody operations of stored links in repository.
 */
class FileLink extends File
{
    /**
     * Returns true if file exists now.
     * If it returns false - file exists sometime at repository.
     *
     * @return boolean
     */
    public function exists()
    {
        return file_exists($this->path);
    }
}
