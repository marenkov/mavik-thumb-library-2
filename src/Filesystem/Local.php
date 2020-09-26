<?php

/**
 * Library for creating thumbnails of images
 *
 * @package Mavik Thumbnails
 * @author Vitalii Marenkov <admin@mavik.com.ua>
 * @copyright 2012-2020 Vitalii Marenkov
 * @license GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Mavik\Thumbnails\Filesystem;

use Mavik\Thumbnails\FileSystem;
use Mavik\Thumbnails\Exception\FileSystemException;
use Mavik\Thumbnails\Exception\ConfigurationException;

/**
 * Operations with file system
 */
class Local implements FileSystem 
{

    /** @var array */
    private $params = [];

    /**
     * @throws ConfigurationException
     */
    public function __construct(array $param)
    {        
        $this->params = $param;
        $this->verifyParams();
    }

    /**
     * @throws ConfigurationException
     */
    protected function verifyParams(): void
    {
        if (empty($this->params['webRootPath'])) {
            throw new ConfigurationException('Parameter webRootPath is not setted.');
        }
        if (!is_string($this->params['webRootPath'])) {
            throw new ConfigurationException('Parameter webRootPath must be string.');
        }
        if (!file_exists($this->params['webRootPath']) || !is_dir($this->params['webRootPath'])) {
            throw new ConfigurationException('Parameter webRootPath is wrong. "' . $this->params['webRootPath'] . '" is not directory.');
        }
    }

    /**
     * Returns full path if path $path exists or null if not
     *
     * @param string $path
     * @return string|null
     */
    public function realPath(string $path): ?string 
    {
        if (file_exists($path)) {
            return realpath($path);
        }
        if ($path[0] == DIRECTORY_SEPARATOR || $this->params['webRootPath'][-1] == DIRECTORY_SEPARATOR) {
            $fullPath = $this->params['webRootPath'] . $path;
        } else {
            $fullPath = $this->params['webRootPath'] . DIRECTORY_SEPARATOR . $path;
        }
        if (file_exists($fullPath)) {
            return realpath($fullPath);
        }
        return null;
    }
    
    public function isDirectory(string $path): bool 
    {
        return is_dir($path);
    }
        
    public function isFile(string $path): bool {
        return is_file($path);
    }
    
    /**
     * @param string $path
     * @param int $mode
     * @throws FileSystemException
     */
    public function makeDirectory(string $path, int $mode = null): void
    {
         if (!mkdir($path, $mode, true)) {
             throw new FileSystemException(sprintf('Can\'t create directory "%s" with mode %o', $path, $mode));
         }
    }

    /**
     * @param string $path
     * @param string $content
     * @param int $mode Permissio
     * @throws FileSystemException
     */
    public function write(string $path, string $content, int $mode = null): void
    {
        if (file_put_contents($path, $content, LOCK_EX) === false) {
            throw new FileSystemException("Cannot write to file '{$path}'.");
        }
        if ($mode && !chmod($path, $mode)) {
            throw new FileSystemException(sprintf('Cannot set mode %o for file "%s".', $mode, $path));
        }
    }
    
    public function read(string $path, int $maxLen = null): string
    {
        return file_get_contents($path, false, null, 0, $maxLen);
    }

    public function pathToUrl(string $path): string {
        $relPath = $this->substr($this->realPath($path), strlen($this->params['webRootPath']));
        return str_replace(DIRECTORY_SEPARATOR, '/', $relPath);        
    }

    /**
    * Convert relative url to path
    *
    * @param string $url
    */
    public function urlToPath(string $url): string
    {
        $urlParts = parse_url($url);
        $path = $urlParts['path'] . ($urlParts['query'] ? "?{$urlParts['query']}" : '');
        return realpath($this->params['webRootPath'] . DIRECTORY_SEPARATOR . $path);
    }

    public function fileSize(string $path): int
    {
        return filesize($path);
    }
}
