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

use Mavik\Thumbnails\DataType\Image;
use Mavik\Thumbnails\DataType\ImageWithThumbnails;

class ImageInfoBuilder {

    /** @var array */
    private $params;

    /** @var FileSystem */
    private $fileSystem;
    
    /** @var ImageFileInformator */
    private $imageFileInformator;

    public function __construct(array $params, FileSystem $fileSystem,  ImageFileInformator $imageFileInformator) {
        $this->params              = $params;
        $this->fileSystem          = $fileSystem;
        $this->imageFileInformator = $imageFileInformator;
    }

    /**
     * @param string $originalSrc Url or path of original image
     * @param int $thumbWidth Width of thumbnail
     * @param int $thumbHeight Height of thumbnail
     * @param float[] $ratios For every value will be created particular thumbnail
     * @return ImageWithThumbnails
     */
    public function build(string $originalSrc, int $thumbWidth, int $thumbHeight, array $ratios = [1]): ImageWithThumbnails
    {
        $imgWithThumb = new ImageWithThumbnails();
        $imgWithThumb->original = $this->makeOriginalImageInfo($originalSrc);
        foreach ($ratios as $ratio) {
            $thumb = $this->makeThumbInfo($imgWithThumb->original, $thumbWidth, $thumbHeight, $ratio);
            if ($thumb) {
                $imgWithThumb->thumbnails[] = $thumb;
            }
        }
        return $imgWithThumb;
    }

    private function makeOriginalImageInfo(string $src): Image
    {      
        if(!empty($path = $this->realPath($src))) {
            $originalImageInfo = $this->makeOriginalImageInfoFromPath($path);
        } else {
            $originalImageInfo = $this->makeOriginalImageInfoFromUrl($src);
        }
        
        list (
            'file_size' => $originalImageInfo->fileSize,
            'type'      => $originalImageInfo->type,
            'height'    => $originalImageInfo->height,
            'width'     => $originalImageInfo->width,            
        ) = $this->imageFileInformator->imageInfo($originalImageInfo);
        
        return $originalImageInfo;
    }

    private function makeOriginalImageInfoFromPath(string $path): Image
    {
        $imageInfo = new Image();
        $imageInfo->isLocal = true;
        $imageInfo->path = $path;
        $imageInfo->url = $this->fileSystem->pathToUrl($path);
        return $imageInfo;
    }
    
    private function makeOriginalImageInfoFromUrl(string $url): Image
    {
        $imageInfo = new Image();
        $imageInfo->isLocal = $this->isUrlLocal($url);
        if($imageInfo->isLocal) {
            return $this->makeOriginalImageInfoFromLocalUrl($url);
        } else {
            return $this->makeOriginalImageInfoFromRemoteUrl($url);
        }
    }
    
    private function makeOriginalImageInfoFromLocalUrl(string $url): Image
    {
        $image = new Image();
        $image->isLocal = true;
        $parsedUrl = parse_url($url);
        $image->url = $parsedUrl['path'] . (empty($parsedUrl['query']) ? '' : "?{$parsedUrl['query']}");
        $image->path = $this->fileSystem->urlToPath($url);
        return $image;        
    }
    
    private function makeOriginalImageInfoFromRemoteUrl(string $url): Image
    {
        $image = new Image();
        $image->isLocal = false;
        if($this->params['copyRemote'] && $this->params['remoteDir'] ) {
            $image->path = $this->copyRemoteFile($src);
            $image->url = $this->fileSystem->pathToUrl($image->path);
        } else {
            // For remote image path is url
            $image->url = str_replace(' ', '+', $src);
            $image->path = $image->url;
        }
        return $image;
    }

    /**
     * Returns real path if $src is path or null
     *
     * @param string $src URL or path
     * @return string|null
     */
    private function realPath(string $src): ?string
    {
        if (
            strpos($src, 'https://') === 0 ||
            strpos($src, 'http://') === 0
        ) {
            return null;
        }
        return $this->fileSystem->realPath($src);
    }

    /**
     * Can be image processed as local?
     * 
     * Returns true if $url is a simle link to a local image.
     * Example: http://test,com/images/img.jpg
     *
     * @param string $url
     * @return boolean
     */
    private function isUrlLocal(string $url): bool
    {
        $siteUri = parse_url($this->params['baseUrl']);
        $imgUri = parse_url($url);

        // If url has query it must be processed as remote
        if (!empty($imgUri['query'])) {
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
     * @todo Реализовать метод getSafeName
     *
     * @param string $src
     */
    private function copyRemoteFile(string $src): string
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
}