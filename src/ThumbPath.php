<?php

/**
 * Creates objects ImageWithThumbnails
 *
 * @package Mavik Thumbnails
 * @author Vitalii Marenkov <admin@mavik.com.ua>
 * @copyright 2012-2020 Vitalii Marenkov
 * @license GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Mavik\Thumbnails;

/**
 * Creates local path for thumbnail
 */
class ThumbPath
{
    
    /** @var array */
    private $params;

    public function __construct($param)
    {
        $this->params = $param;
    }

    /**
     * @param string $path Path or url
     * @param bool $isUrl
     * @param string $suffix Suffix for name of file (example size for thumbnail)
     * @param string $secondExt New extension
     * @return string
     * @throws Exception
     */
    public function create(string $path, bool $isUrl = true, string $suffix = '', string $secondExt = null): string
    {
        if($isUrl) {
            $path = $this->pathFromUrl($path);
        }
        $relPath = $this->pathToRelative($path, $secondExt);

        if($this->params['subDirs']) {
            return $this->withSubDirs($relPath, $suffix, $secondExt);
        } else {
            return $this->withoutSubDirs($relPath, $suffix, $secondExt);
        }
    }
    
    private function pathFromUrl(string $url): string
    {
        $parts = parse_url($url);
        if (empty($parts)) {
            throw new Exception("Can not parse URI '{$path}'", Exception::OTHER);
        }
        $queryCode = sha1($parts['query']);
        return "{$parts['host']}_{$parts['[path']}" . ($queryCode ? "_{$queryCode}" : '');
    }
    
    private function pathToRelative(string $path): string
    {        
        if(strpos($path, $this->params['webDir']) === 0) {
            return substr($path, strlen($this->params['webDir'])+1);
        }
        return $path;
    }
    
    private function withoutSubDirs(string $relPath, string $suffix, string $secondExt = null): string
    {
        $name = str_replace(['/','\\'], '-', $relPath);
        $pathInfo = pathinfo($name);        
        return "{$this->params['webDir']}/{$this->params['thumbDir']}/{$pathInfo['filename']}{$suffix}"
              . ($pathInfo['extension'] ? ".{$pathInfo['extension']}" : '')
              . ($secondExt ? ".{$secondExt}" : '')
        ;
    }
    
    /**
     * @throws Exception
     */
    private function withSubDirs(string $relPath, string $suffix, string $secondExt = null): string
    {
        $pathInfo = pathinfo($relPath);
        $dirname = $this->realDirName($pathInfo['dirname']);
        $resultRelPath = "{$dirname}/{$pathInfo['filename']}"
                         . $suffix
                         . ($pathInfo['extension'] ? ".{$pathInfo['extension']}" : '')
                         . ($secondExt ? ".{$secondExt}" : '')
        ;        
        return str_replace('\\', '/', "{$this->params['webDir']}/{$this->params['thumbDir']}/{$resultRelPath}");
    }
        
    /**
     * Processes ".." and "."
     * 
     * @throws Exception
     */
    private function realDirName(string $dirname): string
    {
        $names = explode('/', str_replace(DIRECTORY_SEPARATOR, '/', $dirname));
        foreach ($names as $index => $name) {
            switch ($name) {
                case '..':
                    if ($index == 0) {
                        throw new Exception("Can't realize real path for '{$dirname}'", Exception::OTHER);
                    }
                    unset($names[$index-1]);
                    unset($names[$index]);
                    break;
                case '.':
                    unset($names[$index]);
                    break;
            }
        }
        return implode('/', $names);
    }
}
