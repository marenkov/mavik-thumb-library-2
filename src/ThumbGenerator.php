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
 * Generator of thumbnails
 *
 * <code>
 *
 * @todo Актуализовать описание параметров
 *
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

    const PARAMS_DEFAULT = [
        'baseUrl'          => '',
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
    protected $params = [];

    /** @var ResizeType[] All used Strategies of resizing */
    protected static $resizeStrategies = [];

    /** @var ResizeType Current Strategy of resizing */
    protected $resizeStrategy = null;

    /** @var GraphicLibrary */
    protected $graphicLibrary = null;

    /** @var FileSystem */
    protected $fileSystem = null;

    /** @var ThumbInfoBuilder */
    protected $thumbInfoBuilder = null;

    /** @var string Path to the directory with images */
    protected $imagesPath = '';

    /**
     * @param array $params
     * @param \Mavik\Thumbnails\FileSystem $fileSystem Object for performing file system operations
     */
    public function __construct(array $params = array(), FileSystem $fileSystem = null)
    {
        $this->setParams(array_replace_recursive(self::PARAMS_DEFAULT, $params));

        if ($fileSystem) {
            $this->fileSystem = $fileSystem;
        } else {
            $this->fileSystem = new \Mavik\Thumbnails\Filesystem\Local($params['fileSystemParams']);
        }

        $this->thumbInfoBuilder = new ThumbInfoBuilder($this->params, $this->fileSystem);
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
     * Get thumbnail, create if it doesn't exist
     *
     * @param string $src Path or URI of image
     * @param int $width Width of thumbnail
     * @param int $height Height of thumbnail
     * @param int $sizeInPixels This parameter for correct working of default sizes
     * @param float $ratio Ratio of real and imaged sizes
     * @return MavikThumbInfo
     */
    public function getThumb(string $src, int $width = 0, int $height = 0, bool $sizeInPixels = true, int $ratios = [1])
    {
        $thumbInfo = $this->thumbInfoBuilder->make($src, $width, $height, $sizeInPixels, $ratios);
        if(!$this->thumbExists($thumbInfo)) {
            $this->testAllocatedMemory($thumbInfo);
            list($x, $y, $widht, $height) = $this->resizeStrategy->getArea($thumbInfo);
            $this->graphicLibrary->createThumbnail($info, $x, $y, $widht, $height);
        }

        return $info;
    }

    /**
     * Create directory if it doesn't exist
     */
    protected function makeDirectory(string $dir)
    {        
        $dir = $this->imagesPath . '/' . $this->params['thumbDir'];
        if (!$this->fileSystem->exists($dir) || !$this->fileSystem->isDirectory($dir)) {
            $this->fileSystem->makeDirectory($dir, $this->params['fileSystemParams']['dirMode']);
            $indexFileContent = '<html><body bgcolor="#FFFFFF"></body></html>';
            $this->fileSystem->write($dir . '/index.html', $indexFileContent, $this->params['fileSystemParams']['fileMode']);
        }
    }

    protected function setGraphicLibrary(string $graphicLibrary, array $graphicLibraryParams = [])
    {
        $class = 'Graphiclibrary' . '\\'. ucfirst($graphicLibrary);
        $this->graphicLibrary = new $class($graphicLibraryParams);
    }

    /**
     * Set resize type
     *
     * @param string $type
     */
    protected function setResizeType(string $type)
    {
        if (empty(self::$resizeStrategies[$type])) {
            $class = 'ResizeType' . '\\' . ucfirst($type);
            self::$resizeStrategies[$type] = new $class;
        }
        $this->resizeStrategy = self::$resizeStrategies[$type];
    }

    /**
     * Get info about original image and thumbnail
     *
     * @param string $src Path or url to original image
     * @param type $width Desired width for thumbnail
     * @param type $height Desired height for thumbnail
     * @param float $ratio Ratio of real and imaged sizes
     * @return MavikThumbInfo
     */
    protected function getThumbInfo($src, $width, $height, $sizeInPixels = true, $ratio = 1)
    {
        $info = new ThumbInfo();
        $this->getOriginalPath($src, $info);
        if (!$info->original->path) {
            return $info;
        }
        $this->getOriginalSize($info);

        if (
            $sizeInPixels && ($width || $height || $this->params['defaultSize']) ||
            $this->params['defaultSize'] == 'all'
        ) {
            $this->setThumbSize($info, $width, $height);
            $this->setThumbRealSize($info, $ratio);
            $this->setThumbPath($info, $info->isLess($info->thumbnail));
        }

        return $info;
    }

    /**
     * Get info about URL and path of original image.
     * And copy remote image if it's need.
     *
     * @param string $src
     * @param MavikThumbInfo
     */
    protected function getOriginalPath($src, MavikThumbInfo $info)
    {
        /*
         *  Is it URL or PATH?
         */
        if(file_exists($src) || file_exists(JPATH_ROOT.'/'.$src)) {
            /*
             *  $src IS PATH
             */
            $info->original->isLocal = true;
            $info->original->path = $this->pathToAbsolute($src);
            $info->original->url = $this->pathToUrl($info->original->path);
        } else {
            /*
             *  $src IS URL
             */
            $info->original->isLocal = $this->isUrlLocal($src);

            if($info->original->isLocal) {
                /*
                 * Local image
                 */
                $uri = JURI::getInstance($src);
                $query = $uri->getQuery();
                $info->original->url = $uri->getPath() . ($query ? "?{$query}" : '');
                $info->original->path = $this->urlToPath($src);
            } else {
                /*
                 * Remote image
                 */
                $src = $this->fullUrl($src);
                if($this->params['copyRemote'] && $this->params['remoteDir'] ) {
                    $this->copyRemoteFile($src, $info);
                } else {
                    // For remote image path is url
                    $info->original->url = str_replace(' ', '+', $src);
                    $info->original->path = $info->original->url;

                }
            }
        }
    }

    /**
     * Copy remote file to local directory
     *
     * @param string $src
     * @param MavikThumbInfo $info
     */
    private function copyRemoteFile($src, MavikThumbInfo $info)
    {
        $localFile = $this->getSafeName($src, $this->params['remoteDir'], '', false);
        if (!file_exists($localFile)) {
            // Copy file
            $buffer = file_get_contents($src);
            JFile::write($localFile, $buffer);
            unset($buffer);
        }
        // New url and path
        $info->original->path = $localFile;
        $info->original->url = $this->pathToUrl($localFile);
    }


    /**
     * Set thumbanil size
     *
     * @param MavikThumbInfo $info
     * @param int $width
     * @param int $height
     */
    protected function setThumbSize(MavikThumbInfo $info, $width, $height)
    {
        if ($this->useDefaultSize($info->original, $width, $height)) {
            if (
                $this->params['defaultWidth'] && $info->original->width > $this->params['defaultWidth']
            ) {
                $width = $this->params['defaultWidth'];
            }

            if (
                $this->params['defaultHeight'] && $info->original->height > $this->params['defaultHeight']
            ) {
                $height = $this->params['defaultHeight'];
            }
        }

        // Set widht or height if it is 0
        if ($width == 0) $width = intval($height * $info->original->width / $info->original->height);
        if ($height == 0) $height = intval($width * $info->original->height / $info->original->width);

        $this->resizeStrategy->setSize($info, $width, $height, $this->params);
    }

    /**
     * Set real size of thumbnail
     *
     * @param MavikThumbInfo $info
     * @param type $ratio
     */
    protected function setThumbRealSize(MavikThumbInfo $info, $ratio)
    {
        if ($info->thumbnail->height * $ratio > $info->original->height) {
            $ratio = $info->original->height / $info->thumbnail->height;
        }
        if ($info->thumbnail->width * $ratio > $info->original->width) {
            $ratio = $info->original->width / $info->thumbnail->width;
        }
        $info->thumbnail->realWidth = floor($info->thumbnail->width * $ratio);
        $info->thumbnail->realHeight = floor($info->thumbnail->height * $ratio);

        foreach ($this->params['ratios'] as $ratio) {
            $info->thumbnails[$ratio] = clone $info->thumbnail;
            $info->thumbnails[$ratio]->realWidth *= $ratio;
            $info->thumbnails[$ratio]->realHeight *= $ratio;
        }
    }

    /**
     * Set path and url of thumbnail
     *
     * @param MavikThumbInfo $info
     * @param bolean $isLess
     */
    protected function setThumbPath(MavikThumbInfo $info, $isLess)
    {
        if (!$isLess) {
            $info->thumbnail->url = $info->original->url;
            return;
        }

        $suffix = "-{$this->params['resizeType']}-{$info->thumbnail->realWidth}x{$info->thumbnail->realHeight}";

        $info->thumbnail->path = $this->getSafeName($info->original->path, $this->params['thumbDir'], $suffix, $info->original->isLocal);
        $info->thumbnail->url = $this->pathToUrl($info->thumbnail->path);
        $info->thumbnail->isLocal = true;

        foreach ($info->thumbnails as $ratio => &$thumbnail) {
            $dir = $this->params['thumbDir'];
            if ($ratio != 1) {
                $dir .= "/@{$ratio}";
            }
            $thumbnail->path = $this->getSafeName($info->original->path, $dir, $suffix, $info->original->isLocal);
            $thumbnail->url = $this->pathToUrl($thumbnail->path);
            $thumbnail->isLocal = true;
        }
    }


    /**
     * Does thumbnail exist and is it actual?
     *
     * @param MavikThumbInfo $info
     * @return boolean
     */
    protected function thumbExists(MavikThumbInfo $info)
    {
        if (!$info->thumbnail->path) {
            return false;
        }

        $originalChangeTime = $this->getOriginalChangeTime($info);
        foreach ($info->thumbnails as $thumbnail) {
            if(
                !JFile::exists($thumbnail->path) ||
                $originalChangeTime > filectime($thumbnail->path)
            ) {
                return false;
            }
        }
        return true;
    }

    /**
     * @param MavikThumbInfo $info
     * @return int timestamp
     */
    protected function getOriginalChangeTime(MavikThumbInfo $info)
    {
        if ($info->original->isLocal || $this->params['copyRemote']) {
            $timestamp = filectime($info->original->path);
        } else {
            $header = get_headers($info->original->url, 1);
            $timestamp = 0;
            if ($header && strstr($header[0], '200') !== false && !empty($header['Last-Modified'])) {
                try {
                    $changeTime = new \DateTime($header['Last-Modified']);
                    $timestamp = $changeTime->getTimestamp();
                } catch (Exception $e) {}
            }
        }
        return (int) $timestamp;
    }

    /**
     * Image is reduced, increased or not changed
     *
     * @param MavikThumbImageInfo $original
     * @return int
     */
    private function isResized(MavikThumbImageInfo $original, $width, $heigh)
    {
        if ($width && $width < $original->width || $heigh && $heigh < $original->height) {
            return 1;
        } elseif (($original->width == $width || !$width) && ($original->height == $heigh || !$heigh)) {
            return 0;
        } else  {
            return -1;
        }
    }

    /**
     * Use default size
     *
     * @param MavikThumbImageInfo $original
     * @param int $width
     * @param int $heigh
     * @return boolean
     */
    private function useDefaultSize(MavikThumbImageInfo $original, $width, $heigh)
    {
        if (empty($this->params['defaultSize'])) {
            return false;
        } elseif ($this->params['defaultSize'] == 'all') {
            return true;
        } elseif ($this->params['defaultSize'] == 'not_resized') {
            return $this->isResized($original, $width, $heigh) == 0;
        }
    }

    /**
     * Get memory limit (bytes)
     *
     * @return int
     */
    protected function getMemoryLimit()
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
     * @param MavikThumbInfo $info
     * @throws Exception
     */
    protected function testAllocatedMemory(MavikThumbInfo $info)
    {
        $allocatedMemory = $this->getMemoryLimit() - memory_get_usage(true);
        $neededMemory = $info->original->width * $info->original->height * 4;
        foreach ($info->thumbnails as $thumbnail) {
            $neededMemory += $thumbnail->width * $thumbnail->height * 4;
        }
        $neededMemory *= 1.25; // +25%
        if ($neededMemory >= $allocatedMemory) {
            throw new Exception(JText::_('Not enough memory'), self::ERROR_NOT_ENOUGH_MEMORY);
        }
    }

    /**
     * @param string $url
     * @return string
     */
    protected function fullUrl($url)
    {
        $uri = new \Joomla\Uri\Uri($url);
        if (!$uri->getHost()) {
            $path = $uri->getPath();
            $query = $uri->getQuery();
            $basePath = JUri::base(true);
            if ($basePath && strpos($path, $basePath) === 0) {
                $path = substr($path, strlen($basePath) + 1);
            }
            return JUri::base() . $path . ($query ? "?{$query}" : '');
        }
        return $url;
    }
}
