<?php

/**
 * Library for creating thumbnails of images
 *
 * @package Mavik Thumbnails
 * @author Vitalii Marenkov <admin@mavik.com.ua>
 * @copyright 2012-2020 Vitalii Marenkov
 * @license GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Mavik\Thumbnails;

class FileName
{
    /** @var array */
    private $params;

    public function __construct(array $params): void
    {
        $this->params = $params;
    }

    /**
     * @param string $path Original path or url to file
     * @param string $destDir Destination directory
     * @param string $suffix Suffix for name of file (example size for thumbnail)
     * @param string $addExt Additional extension
     * @return string 
     */
    public function make(string $path, string $destDir, string $suffix = '', bool $pathIsUrl = true, string $addExt = null)
    {
        if($pathIsUrl) {
            $path = $this->urlToPath($path);
        }                
        
        $path = $this->absolutePathToRelative($path);
       
        if(!$this->params['subDirs']) {
            // Without subdirs
             
        } else {
            // With subdirs
        }
        
        return $path;
    }

    private function urlToPath(string $url): string
    {
        $urlParts = parse_url($url);
        $queryCode = empty($urlParts['query']) ? '' : md5($urlParts['query']);
        return $urlParts['host'] . '/' . $urlParts['path'] . ($queryCode ? "_{$queryCode}" : '');
    }
    
    private function absolutePathToRelative(string $path): string
    {
        $webDir = $this->params['webDir'];
        if(strpos($path, $webDir) === 0) {
            return substr($path, strlen($webDir) + 1);
        }
        return $path;
    }
    
    private function withoutSubdirs(string $path, string $destDir, string $suffix = null, string $addExt = null): array
    {
        $webDir = $this->params['webDir'];
        $pathinfo = pathinfo($name, PATHINFO_DIRNAME | PATHINFO_FILENAME | PATHINFO_EXTENSION);
        $ext = $pathinfo['extension'] ?? '';
        
        /**
         * @todo Продолжить здесь
         */
        
        $name = str_replace(array('/','\\'), '_', str_replace('_', '__', $path));
        
               
        $name =  $pathinfo['filename'] . $suffix . ($ext ? ".{$ext}" : '') . ($secondExt ? ".{$secondExt}" : '');
        return [
            'dirname' => "{$webDir}/{$destDir}",
            'basename' => $name,
            'fullname' => "{$webDir}/{$distDir}/{$name}",
        ];
    }
    
    private function withSubdirs(string $path, string $destDir, string $suffix = null, string $addExt = null): array
    {
        $webDir = $this->params['webDir'];
        $pathinfo = pathinfo($path, PATHINFO_DIRNAME | PATHINFO_FILENAME | PATHINFO_EXTENSION);
        $ext = $pathinfo['extension'] ?? '';
        $dirname = str_replace('\\', '/', "{$webDir}/{$destDir}/{$pathinfo['dirname']}");
        $name = $pathinfo['filename'] . $suffix . ($ext ? ".{$ext}" : '') . ($addExt ? ".{$addExt}" : '');
        
        return [
            'dirname' => $dirname,
            'basename' => $name,
            'fullname' => "{$dirname}/{$name}",
        ];
    }
}