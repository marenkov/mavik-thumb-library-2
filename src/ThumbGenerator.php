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

use Mavik\Thumbnails\DataType\ImageWithThumbnails;

/**
 * Generator of thumbnails
 *
 * @todo Актуализовать описание параметров
 * <code>
 * Params {
 *   thumbDir: Directory for thumbnails
 *   subDirs: Create subdirectories in thumbnail derectory
 *   copyRemote: Copy remote images
 *   remoteDir: Directory for copies of remote images and meta-files
 *   quality: Quality of jpg-images
 *   resizeType: Method of resizing
 *   defaultSize: Use default size ''|'all'|'not_resized'
 *   defaultWidth: Default width
 *   defaultHeight: Default heigh
 *   ratios: Ratios are used for generation of thumbnails for high resolution displays
 * }
 * </code>
 */
class ThumbGenerator {

    const MAX_BYTES_BY_PIXEL = 4;
    
    const PARAMS_DEFAULT = [
        'baseUrl'          => '',
        'webDir'           => '',
        'thumbDir'         => 'images/thumbnails',
        'subDirs'          => true,
        'copyRemote'       => false,
        'remoteDir'        => 'images/remote',
        'resizeType'       => 'fill',
        'defaultSize'      => '',
        'defaultWidth'     => null,
        'defaultHeight'    => null,
        'graphicLibrary'   => 'gd2',
        'ratios'           => [1],
        'fileSystemParams' => [
            'dirMode'     => 0755,
            'fileMode'    => 0644,
            'webRootPath' => '',
        ],
        'graphicLibraryParams'=> [
            'quality' => 90,
        ],
    ];

    /** @var array */
    protected $params;

    /** @var ResizeType[] All used Strategies of resizing */
    protected static $resizeStrategies;

    /** @var ResizeType Current Strategy of resizing */
    protected $resizeStrategy;

    /** @var GraphicLibrary */
    protected $graphicLibrary;

    /** @var FileSystem */
    protected $fileSystem;

    /** @var ImageInfoBuilder */
    protected $thumbInfoBuilder;

    /**
     * @param array $params
     * @param FileSystem $fileSystem Object for executing file system operations
     */
    public function __construct(array $params = array(), FileSystem $fileSystem = null)
    {
        $this->setParams(array_replace_recursive(self::PARAMS_DEFAULT, $params));

        if ($fileSystem) {
            $this->fileSystem = $fileSystem;
        } else {
            $this->fileSystem = new Filesystem\Local($params['fileSystemParams']);
        }

        $this->thumbInfoBuilder = new ImageInfoBuilder(
            $this->params,
            $this->fileSystem,
            new ImageFileInformator($fileSystem, $params)
        );
    }

    /**
     * Set parameters
     * @param array $params
     */
    public function setParams(array $params) {
        $this->params = array_replace_recursive($this->params, $params);
        
        if (isset($params['resizeType'])) {
            $this->setResizeType($params['resizeType']);
        }

        if(isset($params['thumbDir'])) {
            $this->makeDirectory($params['thumbDir']);
        }

        if(isset($params['remoteDir'])) {
            $this->makeDirectory($params['remoteDir']);
        }

        if(isset($params['graphicLibrary'])) {
            $this->setGraphicLibrary($params['graphicLibrary'], $params['graphicLibraryParams']);
        }
    }

    /**
     * Get thumbnails, create if thay don't exist
     *
     * @param string $src Path or URI of image
     * @param int $width Width of thumbnail
     * @param int $height Height of thumbnail
     * @param float[] $ratios Ratios of real and imaged sizes
     * @return ImageWithThumbnails
     */
    public function getThumbnails(string $src, int $width = 0, int $height = 0, int $ratios = [1]): ImageWithThumbnails
    {
        $imgWithThumb = $this->thumbInfoBuilder->build($src, $width, $height, $ratios);
        if (!$imgWithThumb->allThumbnailsExist()) {
            $this->testAllocatedMemory($imgWithThumb);
            list($x, $y, $widht, $height) = $this->resizeStrategy->getArea($imgWithThumb);
            $this->graphicLibrary->createThumbnail($imgWithThumb, $x, $y, $widht, $height);
        }     
        return $imgWithThumb;
    }

    /**
     * @param string $dir
     * @throws Exception\FileSystemException
     */
    protected function makeDirectory(string $dir)
    {        
        if (!$this->fileSystem->isDirectory($dir)) {
            $this->fileSystem->makeDirectory($dir, $this->params['fileSystemParams']['dirMode']);
            $indexFileContent = '<html><body bgcolor="#FFFFFF"></body></html>';
            $this->fileSystem->write($dir . '/index.html', $indexFileContent, $this->params['fileSystemParams']['fileMode']);
        }
    }

    /**
     * @param string $graphicLibrary
     * @param array $graphicLibraryParams
     * @throws \Mavik\Thumbnails\Exception
     */
    protected function setGraphicLibrary(string $graphicLibrary, array $graphicLibraryParams = [])
    {
        $class = 'Graphiclibrary' . '\\'. ucfirst($graphicLibrary);
        if (!class_exists($class)) {
            throw new Exception(
                "Configuration error: graphic library '{$graphicLibrary}' doesn't exist.",
                Exception::CONFIGURATION
            );
        }
        
        $this->graphicLibrary = new $class($graphicLibraryParams);
    }

    /**
     * Set resize type
     *
     * @param string $type
     * @throws \Mavik\Thumbnails\Exception
     */
    protected function setResizeType(string $type)
    {
        if (empty(self::$resizeStrategies[$type])) {
            $class = 'ResizeType' . '\\' . ucfirst($type);
            if (!class_exists($class)) {
                throw new Exception(
                    "Configuration error: resize type '{$type}' dosn't exist.",
                    Exception::CONFIGURATION
                );
            }
            self::$resizeStrategies[$type] = new $class;
        }
        $this->resizeStrategy = self::$resizeStrategies[$type];
    }

    /**
     * Get memory limit (bytes)
     *
     * @return int
     */
    private function getMemoryLimit()
    {
        $sizeStr = ini_get('memory_limit');
        switch (substr ($sizeStr, -1))
        {
            case 'M': case 'm': return (int) $sizeStr * 1048576;
            case 'K': case 'k': return (int) $sizeStr * 1024;
            case 'G': case 'g': return (int) $sizeStr * 1073741824;
            default: return (int) $sizeStr;
        }
    }

    /**
     * @param MavikThumbInfo $image
     * @throws Mavik\Thumbnails\Exception
     */
    protected function testAllocatedMemory(ImageWithThumbnails $image)
    {
        $allocatedMemory = $this->getMemoryLimit() - memory_get_usage(true);
        $neededMemory = $image->original->width * $image->original->height * self::MAX_BYTES_BY_PIXEL;
        foreach ($image->thumbnails as $thumbnail) {
            $neededMemory += $thumbnail->width * $thumbnail->height * self::MAX_BYTES_BY_PIXEL;
        }
        $neededMemory *= 1.25; // +25%
        if ($neededMemory > $allocatedMemory) {
            throw new Exception(JText::_('Not enough memory'), Exception::ERROR_NOT_ENOUGH_MEMORY);
        }
    }
}