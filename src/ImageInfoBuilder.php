<?php

/**
 * Creates objects ThumbInfo
 *
 * @package Mavik Thumbnails
 * @author Vitalii Marenkov <admin@mavik.com.ua>
 * @copyright 2012-2020 Vitalii Marenkov
 * @license GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Mavik\Thumbnails;

use Mavik\Thumbnails\DataType\Image;
use Mavik\Thumbnails\DataType\ImageWithThumbnails;

class ImageInfoBuilder {

    /** @var array */
    protected $params;

    /** @var FileSystem */
    protected $fileSystem;
    
    /** @var ImageFileInfo */
    protected $imageFileInfo;

    public function __construct(array $params, FileSystem $fileSystem) {
        $this->params        = $params;
        $this->fileSystem    = $fileSystem;
        $this->imageFileInfo = new ImageFileInfo($fileSystem, $params);
    }

    /**
     * @param string $originalSrc Url or path of original image
     * @param int $thumbWidth Width of thumbnail
     * @param int $thumbHeight Height of thumbnail
     * @param float[] $ratios For every value will be created particular thumbnail
     * @return ImageWithThumbnails
     */
    public function make(string $originalSrc, int $thumbWidth, int $thumbHeight, array $ratios = [1]): ImageWithThumbnails
    {
        $thumbInfo = new ImageWithThumbnails();
        $thumbInfo->original = $this->makeOriginalImageInfo($originalSrc);
        foreach ($ratios as $ratio) {
            $thumbInfo = new Image();
            
            $thumbInfo->thumbnails[] = $thumbInfo;            
        }

        return $thumbInfo;
    }

    protected function makeOriginalImageInfo(string $src): Image
    {      
        if(!empty($path = $this->isPath($src))) {
            $originalImageInfo = $this->makeOriginalImageInfoFromPath($src);
        } else {
            $originalImageInfo = $this->makeOriginalImageInfoFromUrl($src);
        }
        
        list (
            'file_size' => $originalImageInfo->fileSize,
            'type'      => $originalImageInfo->type,
            'height'    => $originalImageInfo->height,
            'width'     => $originalImageInfo->width,            
        ) = $this->imageFileInfo->imageInfo($originalImageInfo);
        
        return $originalImageInfo;
    }

    protected function makeOriginalImageInfoFromPath(string $path): Image
    {
        $imageInfo = new Image();
        $imageInfo->isLocal = true;
        $imageInfo->path = $path;
        $imageInfo->url = $this->fileSystem->pathToUrl($path);
        return $imageInfo;
    }
    
    protected function makeOriginalImageInfoFromUrl(string $url): Image
    {
        $imageInfo = new Image();
        $imageInfo->isLocal = $this->isUrlLocal($url);
        if($imageInfo->isLocal) {
            // Local image
            $parsedUrl = parse_url($url);
            $imageInfo->url = $parsedUrl['path'] . ($parsedUrl['query'] ? "?{$parsedUrl['query']}" : '');
            $imageInfo->path = $this->urlToPath($url);
        } else {
            // Remote image
            if($this->params['copyRemote'] && $this->params['remoteDir'] ) {
                $imageInfo->path = $this->copyRemoteFile($src);
                $imageInfo->url = $this->fileSystem->pathToUrl($imageInfo->path);
            } else {
                // For remote image path is url
                $imageInfo->url = str_replace(' ', '+', $src);
                $imageInfo->path = $imageInfo->url;
            }
        }
        return $imageInfo;
    }

    /**
     * Returns real path if $src is path or null
     *
     * @param string $src URL or path
     * @return string|null
     */
    protected function isPath(string $src): ?string
    {
        // Don't touch file system is it is clear that it is URL
        if (
            strpos($src, 'https://') === 0 ||
            strpos($src, 'http://') === 0
        ) {
            return null;
        }
        return $this->fileSystem->realPath($src);
    }

    /**
     * Is URL local?
     *
     * @param string $url
     * @return boolean
     */
    protected function isUrlLocal(string $url): bool
    {
        $siteUri = parse_url($this->params['baseUrl']);
        $imgUri = parse_url($url);

        // If url has query it must be processed as remote
        if ($imgUri['query']) {
            return false;
        }

        // ignore www in host name
        $siteHost = preg_replace('/^www\./', '', $siteUri['host']);
        $imgHost = preg_replace('/^www\./', '', $imgUri['host']);

        return (empty($imgHost) || $imgHost == $siteHost);
    }

    /**
     * Copy remote file to local directory
     *
     * @param string $src
     */
    protected function copyRemoteFile($src)
    {
        $localFile = $this->getSafeName($src, $this->params['remoteDir'], '', false);
        if (!file_exists($localFile)) {
            /** @todo Replace to stream processing */
            $buffer = file_get_contents($src);
            $this->fileSystem->write($localFile, $buffer);
            unset($buffer);
        }
        return $localFile;
    }

    /**
     * Get safe name
     *
     * @param string $path Path to file
     * @param string $dir Directory for result file
     * @param string $suffix Suffix for name of file (example size for thumbnail)
     * @param string $secondExt New extension
     * @return string
     */
    protected function getSafeName($path, $dir, $suffix = '', $isLocal = true, $secondExt = null)
    {
        if(!$isLocal) {
            $uri = JURI::getInstance($path);
            $query = $uri->getQuery();
            $queryCode = sha1($query);
            $path = $uri->getHost().$uri->getPath() . ($queryCode ? "_{$queryCode}" : '');
        }

        // Absolute path to relative
        if(strpos($path, JPATH_SITE) === 0) $path = substr($path, strlen(JPATH_SITE)+1);

        $lang = JFactory::getLanguage();

        if(!$this->params['subDirs']) {
            // Without subdirs
            $name = str_replace(array('/','\\'), '-', $path);
            $ext = JFile::getExt($name);
            $name = JFile::stripExt($name).$suffix.($ext ? '.'.$ext : '').($secondExt ? '.'.$secondExt : '');
            $path = JPATH_ROOT."/{$dir}/{$name}";
        } else {
            // With subdirs
            $name = JFile::getName($path);
            $ext = JFile::getExt($name);
            $name = JFile::stripExt($name).$suffix.($ext ? '.'.$ext : '').($secondExt ? '.'.$secondExt : '');
            $path = JPATH_BASE."/{$dir}/{$path}";
            $path = str_replace('\\', '/', $path);
            $path = substr($path, 0, strrpos($path, '/'));
            if(!JFolder::exists($path)) {
                JFolder::create($path);
                $indexFile = '<html><body bgcolor="#FFFFFF"></body></html>';
                JFile::write($path.'/index.html', $indexFile);
            }
            $path = $path . '/' . $name;
        }

        return $path;
    }
    
    protected function getImageFileInfo(Image $imageInfo): array
    {
        $useInfoFile = !$imageInfo->isLocal && empty($this->params['copyRemote']) && !empty($this->params['remoteDir']);
        if($useInfoFile) {
            $infoFilePath = $this->getSafeName($info->url, $this->params['remoteDir'], '', false, 'info');
            if($this->fileSystem->isFile($infoFilePath)) {
                $imageSize = unserialize($this->fileSystem->read($infoFilePath));
            }
        }        
    }
}