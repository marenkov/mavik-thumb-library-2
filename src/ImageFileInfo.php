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

/**
 * Get information about image file
 */
class ImageFileInfo
{
    
    /** @var Filesystem */
    protected $fileSystem = null;

    public function __construct(Filesystem $fileSystem)
    {
        $this->fileSystem = $fileSystem;
    }

    /**
     * Get size and type of image and size of file
     * 
     * Returns array(
     *  'width'    => <width>,
     *  'height'   => <height>,
     *  'type'     => <constant IMG_XXX>,
     *  'fileSize' => <size of file in bytes>,
     * )
     * 
     * @param ImageInfo $info
     * @return array
     */
    public function imageInfo(ImageInfo $info): array
    {
        if ($info->isLocal) {
            $imageSize = $this->imageSizeFromFile($info->path);
            $fileSize = $this->fileSystem->fileSize($info->path);
        } else {
            list (
                'image_size' => $imageSize,
                'file_size'  => $fileSize,
            ) = $this->imageInfoFromUrl($info->url);
        }
        return [
            'width'    => isset($imageSize[0]) ? $imageSize[0] : null,
            'height'   => isset($imageSize[1]) ? $imageSize[1] : null,
            'type'     => isset($imageSize[3]) ? $imageSize[3] : null,
            'fileSize' => $fileSize,
        ];
    }
    
    /**
     * Returns array(
     *    'file_size' => <file size in bytes>
     *    'image_size' => <result of getimagesize()>
     * )
     * 
     * @param string $url
     * @return array
     */
    protected function imageInfoFromUrl(string $url): array
    {
        $context = stream_context_create([
            'http' => [
                'header' => 'Range: bytes=0-32768',
            ]
        ]);
        $imageData = file_get_contents($url, false, $context, 0, 32768);
        return [
            'file_size' => $this->fileSizeFromHttpHeaders($http_response_header), // $http_response_header is setted by PHP in file_get_contents()
            'image_size' => getimagesizefromstring($imageData),
        ];
    }
    
    protected function fileSizeFromHttpHeaders(array $httpHeaders = null): ?int
    {
        $parsedHeaders = $this->parseHttpHeaders($httpHeaders);
        if (!isset($parsedHeaders['response_code'])) {
            return null;
        }
        if (
            $parsedHeaders['response_code'] == 206 &&
            isset($parsedHeaders['content-range']) &&
            strpos($parsedHeaders['content-range'], 'bytes') !== false
        ) {
            $parts = explode('/', $parsedHeaders['content-range']);
            return (int)$parts[1] ?? null;            
        }
        if (
            $parsedHeaders['response_code'] == 200 &&
            isset($parsedHeaders['content-length']) &&
            is_numeric($parsedHeaders['content-length'])
        ) {
            return (int)$parsedHeaders['content-length'];
        }
        return null;
    }

    protected function parseHttpHeaders(array $httpHeaders = null): array
    {
        $result = [];
        if (!is_array($httpHeaders)) {
            return $result;
        }
        foreach ($httpHeaders as $line) {
            $parts = explode(':', $line, 2);
            if (isset($parts[1])) {
                $result[strtolower(trim($parts[0]))] = trim($parts[1]);
            } else {
                $result[] = $line;
                if (preg_match('#HTTP/[0-9\.]+\s+([0-9]+)#',$line, $matches)) {
                    $result['response_code'] = intval($matches[1]);
                }
            }
        }
    }
    
    protected function imageSizeFromFile(string $path)
    {
        $imagedata = $this->fileSystem->read($path, 32768);
        return getimagesizefromstring($imagedata);
    }
}