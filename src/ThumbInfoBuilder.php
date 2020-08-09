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

class ThumbInfoBuilder {

    /** @var array */
    protected $params = [];

    /** @var FileSystem */
    protected $fileSystem = null;

    public function __construct(array $params, FileSystem $fileSystem) {
        $this->params = $params;
        $this->fileSystem = $fileSystem;
    }

    /**
     * @param string $imgSrc Url or path of original image
     * @param int $thumbWidth Width of thumbnail
     * @param int $thumbHeight Height of thumbnail
     * @param float[] $ratios For every value will be created particular thumbnail
     * @return ThumbInfo
     */
    public function make(string $imgSrc, int $thumbWidth, int $thumbHeight, array $ratios = [1]) {
        $thumbInfo = new ThumbInfo();
        $thumbInfo->original = new ImageInfo();                
        $this->initOriginalImage($thumbInfo, htmlspecialchars_decode($src));

        if (!$info->original->path) {
            return $info;
        }
        $this->initOriginalSize();

        foreach ($ratios as $ratio) {
            $thumbInfo->thumbnails[] = ImageInfo();
            /**
             * @todo Инициализировать аттрибуты
             */
        }

        return $thumbInfo;
    }


    /**
     * Get info about URL and path of original image.
     * And copy remote image if it's need.
     *
     * @param string $src
     */
    protected function initOriginalImage(ThumbInfo $thumbInfo, string $src)
    {
        /*
         *  Is it URL or PATH?
         */
        if(file_exists($src) || file_exists(JPATH_ROOT.'/'.$src)) {
            /*
             *  $src IS PATH
             */
            $thumbInfo->original->local = true;
            $thumbInfo->original->path = $this->pathToAbsolute($src);
            $thumbInfo->original->url = $this->pathToUrl($info->original->path);
        } else {
            /*
             *  $src IS URL
             */
            $info->original->local = $this->isUrlLocal($src);

            if($info->original->local) {
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
     * Get absolute path
     *
     * @param string $path
     * @return string
     */
    protected function pathToAbsolute($path)
    {
        // $paht is c:\<path> or \<path> or /<path> or <path>
        if (!preg_match('/^\\\|\/|([a-z]\:)/i', $path)) $path = JPATH_ROOT.'/'.$path;
        return realpath($path);
    }

    /**
     * Get URL from absolute path
     *
     * @param string $path
     * @return string
     */
    protected function pathToUrl($path)
    {
        $base = JURI::base(true);
        $path = $base.substr($path, strlen(JPATH_SITE));

        return str_replace(DIRECTORY_SEPARATOR, '/', $path);
    }

    /**
     * Is URL local?
     *
     * @param string $url
     * @return boolean
     */
    protected function isUrlLocal($url)
    {
        $siteUri = JFactory::getURI();
        $imgUri = JURI::getInstance($url);

        // If url has query it must be processed as remote
        if ($imgUri->getQuery()) {
            return false;
        }

        $siteHost = $siteUri->getHost();
        $imgHost = $imgUri->getHost();
        // ignore www in host name
        $siteHost = preg_replace('/^www\./', '', $siteHost);
        $imgHost = preg_replace('/^www\./', '', $imgHost);

        return (empty($imgHost) || $imgHost == $siteHost);
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

    /**
    * Convert local url to path
    *
    * @param string $url
    */
    protected static function urlToPath($url)
    {
        $imgUri = JURI::getInstance($url);
        $query = $imgUri->getQuery();
        $path = $imgUri->getPath() . ($query ? "?{$query}" : '');
        $base = JURI::base(true);
        if($base && strpos($path, $base) === 0) {
            $path = substr($path, strlen($base));
        }
        return realpath(JPATH_ROOT.'/'.$path);
    }

    /**
     * Get size and type of original image
     *
     * @param MavikThumbInfo $info
     * @param boolean $recursive
     */
    protected function getOriginalSize(MavikThumbInfo $info, $recursive = false)
    {
        // Get size and type of image. Use info-file for remote image
        $useInfoFile = !$info->original->local && !$this->params['copyRemote'] && $this->params['remoteDir'];
        if($useInfoFile) {
            $infoFile = $this->getSafeName($info->original->url, $this->params['remoteDir'], '', false, 'info');

            if(file_exists($infoFile)) {
                $size = unserialize(file_get_contents($infoFile));
                $info->original->size = isset($size['filesize']) ? $size['filesize'] : null;
            }

            if (!isset($size[0])) {
                $size = getimagesize($info->original->url);
                $info->original->size = JFilesystemHelper::remotefsize($info->original->url);
                $size['filesize'] = $info->original->size;
                if($useInfoFile) {
                    JFile::write($infoFile, serialize($size));
                }
            }
        } else {
            $size = @getimagesize($info->original->path);
            $info->original->size = @filesize($info->original->path);
        }

        /**
         * If url point to script, set local=false and call function again
         */
        if (!isset($size[0]) && !$recursive) {
            $info->original->local = false;
            $info->original->url = $this->fullUrl($info->original->url);
            $info->original->path = $info->original->url;
            $size = $this->getOriginalSize($info, true);
        }

        // Put values to $info
        $info->original->width = isset($size[0]) ? $size[0] : null;
        $info->original->height = isset($size[1]) ? $size[1] : null;
        $info->original->type = isset($size['mime']) ? $size['mime'] : null;

        return $size; // for recursive
    }

}
