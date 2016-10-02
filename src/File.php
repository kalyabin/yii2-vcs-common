<?php
namespace VcsCommon;

use VcsCommon\exception\CommonException;
use yii\base\Object;
use yii\helpers\FileHelper;
use yii\helpers\StringHelper;

/**
 * Implements file operations.
 *
 * File may be exists now, or sometime removed.
 * This object implements everybody operations of stored files in repository.
 */
class File extends Object
{
    const STATUS_ADDITION = 'A';

    const STATUS_COPIED = 'C';

    const STATUS_DELETION = 'D';

    const STATUS_MODIFIED = 'M';

    const STATUS_RENAMING = 'R';

    const STATUS_TYPED = 'T';

    const STATUS_UNMERGED = 'U';

    const STATUS_UNKNOWN = 'X';

    /**
     * @var string modification status code in specified revision
     */
    protected $status;

    /**
     * @var string file name
     */
    protected $name;

    /**
     * @var string file path
     */
    protected $path;

    /**
     * @var string relative path
     */
    protected $relativePath;

    /**
     * @var BaseRepository
     */
    protected $repository;

    /**
     * Constructor
     *
     * @param string $pathName
     * @param BaseRepository $repository
     * @param string|null $status file status in revision
     *
     * @throws CommonException
     */
    public function __construct($pathName, BaseRepository $repository, $status = null)
    {
        $this->name = basename($pathName);
        $this->path = FileHelper::normalizePath($pathName);
        // first character for status
        $this->status = !is_null($status) ? substr($status, 0, 1) : $status;
        $this->repository = $repository;
        if (!StringHelper::startsWith($this->path, $repository->getProjectPath())) {
            throw new CommonException("Path {$this->path} outband of repository");
        }
        $this->relativePath = substr($this->path, strlen($repository->getProjectPath()));
        parent::__construct([]);
    }

    /**
     * Returns file path
     *
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * Returns directory path
     *
     * @return string
     */
    public function getDirectory()
    {
        return dirname($this->path);
    }

    /**
     * Returns file base name
     *
     * @return string
     */
    public function getFileName()
    {
        return $this->name;
    }

    /**
     * Return path name without repository path
     *
     * @return string
     */
    public function getPathname()
    {
        return ltrim(mb_substr($this->path, mb_strlen($this->repository->getProjectPath())), DIRECTORY_SEPARATOR);
    }

    /**
     * Returns revision status.
     *
     * Null if has no modifications.
     *
     * @return string|null
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Returns true if file exists now.
     * If it returns false - file exists sometime at repository.
     *
     * @return boolean
     */
    public function exists()
    {
        return is_file($this->path);
    }

    /**
     * Returns file size if it exists.
     *
     * @return integer
     */
    public function getSize()
    {
        if ($this->exists()) {
            return filesize($this->path);
        }
        return null;
    }

    /**
     * Returns file permissions if it exists.
     *
     * @return string
     */
    public function getPermissions()
    {
        if ($this->exists()) {
            /**
             * @see http://php.net/manual/ru/function.fileperms.php
             */
            $perms = fileperms($this->path);
            if (($perms & 0xC000) == 0xC000) {
                // Сокет
                $info = 's';
            } elseif (($perms & 0xA000) == 0xA000) {
                // Символическая ссылка
                $info = 'l';
            } elseif (($perms & 0x8000) == 0x8000) {
                // Обычный
                $info = '-';
            } elseif (($perms & 0x6000) == 0x6000) {
                // Специальный блок
                $info = 'b';
            } elseif (($perms & 0x4000) == 0x4000) {
                // Директория
                $info = 'd';
            } elseif (($perms & 0x2000) == 0x2000) {
                // Специальный символ
                $info = 'c';
            } elseif (($perms & 0x1000) == 0x1000) {
                // Поток FIFO
                $info = 'p';
            } else {
                // Неизвестный
                $info = 'u';
            }

            // Владелец
            $info .= (($perms & 0x0100) ? 'r' : '-');
            $info .= (($perms & 0x0080) ? 'w' : '-');
            $info .= (($perms & 0x0040) ?
                        (($perms & 0x0800) ? 's' : 'x' ) :
                        (($perms & 0x0800) ? 'S' : '-'));

            // Группа
            $info .= (($perms & 0x0020) ? 'r' : '-');
            $info .= (($perms & 0x0010) ? 'w' : '-');
            $info .= (($perms & 0x0008) ?
                        (($perms & 0x0400) ? 's' : 'x' ) :
                        (($perms & 0x0400) ? 'S' : '-'));

            // Мир
            $info .= (($perms & 0x0004) ? 'r' : '-');
            $info .= (($perms & 0x0002) ? 'w' : '-');
            $info .= (($perms & 0x0001) ?
                        (($perms & 0x0200) ? 't' : 'x' ) :
                        (($perms & 0x0200) ? 'T' : '-'));

            return $info;
        }
        return '----------';
    }

    /**
     * Returns relative path
     *
     * @return string
     */
    public function getRelativePath()
    {
        return $this->relativePath;
    }
}
