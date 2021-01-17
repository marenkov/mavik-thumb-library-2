<?php
namespace Mavik\Thumbnails;

use PHPUnit\Framework\TestCase;
use Mavik\Thumbnails\DataType\Image;
use Mavik\Thumbnails\Tests\HttpServer;

class ImageFileInfoTest extends TestCase
{
    /**
     * @var ImageFileInformator
     */
    static protected $imageFileInfo;
    
    public static function setUpBeforeClass(): void
    {
        $webRoot = __DIR__ . '/../resources/images';
        HttpServer::start($webRoot);
        self::$imageFileInfo = new ImageFileInformator(new Filesystem\Local([
            'webRootPath' => $webRoot,
        ]));
    }
    
    /**
     * @covers Mavik\Thumbnails\ImageFileInfo::imageInfo
     * @dataProvider correctImagesProvider
     */
    public function testImageInfoCorrectImages(bool $isLocal, string $src, array $trueResult)
    {
        $info = new Image();
        $info->isLocal = $isLocal;
        if ($isLocal) {
            $info->path = $src;
        } else {
            $info->url = $src;
        }
        
        $result = self::$imageFileInfo->imageInfo($info);
        
        $this->assertEquals($trueResult, $result);
    }

    /**
     * @covers Mavik\Thumbnails\ImageFileInfo::imageInfo
     * @dataProvider wrongImagesProvider
     */    
    public function testImageInfoWrongImages(bool $isLocal, string $src, string $messageRegExp)
    {
        $this->expectExceptionMessageMatches($messageRegExp);
        
        $info = new Image();
        $info->isLocal = false;
        if ($isLocal) {
            $info->path = $src;
        } else {
            $info->url = $src;
        }
        
        self::$imageFileInfo->imageInfo($info);
    }

    public function correctImagesProvider()
    {
        return [
            'apple.jpg' => [
                true,
                __DIR__ . '/../resources/images/apple.jpg',
                [
                    'width'     => 1200,
                    'height'    => 1200,
                    'type'      => IMAGETYPE_JPEG,
                    'file_size' => 224643,
                ]
            ],
            'butterfly_with_transparent_bg.png' => [
                true,
                __DIR__ . '/../resources/images/butterfly_with_transparent_bg.png',
                [
                    'width'     => 1280,
                    'height'    => 1201,
                    'type'      => IMAGETYPE_PNG,
                    'file_size' => 308897,    
                ]
            ],
            'chrismas_tree_with_transparent_bg.png' => [
                true,
                __DIR__ . '/../resources/images/chrismas_tree_with_transparent_bg.png',
                [
                    'width'     => 1615,
                    'height'    => 1920,
                    'type'      => IMAGETYPE_PNG,
                    'file_size' => 141327,    
                ]
            ],
            'pinapple-animated.gif' => [
                true,
                __DIR__ . '/../resources/images/pinapple-animated.gif',
                [
                    'width'     => 457,
                    'height'    => 480,
                    'type'      => IMAGETYPE_GIF,
                    'file_size' => 157012,
                ]
            ],
            'snowman-pixel.gif' => [
                true,
                __DIR__ . '/../resources/images/snowman-pixel.gif',
                [
                    'width'     => 700,
                    'height'    => 1300,
                    'type'      => IMAGETYPE_GIF,
                    'file_size' => 53777,
                ]
            ],
            'tree_with_white_background.jpg' => [
                true,
                __DIR__ . '/../resources/images/tree_with_white_background.jpg',
                [
                    'width'     => 1280,
                    'height'    => 1280,
                    'type'      => IMAGETYPE_JPEG,
                    'file_size' => 181304,
                ]
            ],
            'house.webp' => [
                true,
                __DIR__ . '/../resources/images/house.webp',
                [
                    'width'     => 1536,
                    'height'    => 1024,
                    'type'      => IMAGETYPE_WEBP,
                    'file_size' => 644986,
                ]
            ],
            'beach.webp' => [
                true,
                __DIR__ . '/../resources/images/beach.webp',
                [
                    'width'     => 730,
                    'height'    => 352,
                    'type'      => IMAGETYPE_WEBP,
                    'file_size' => 69622,
                ]
            ],
            'http://localhost:8888/apple.jpg' => [
                false,
                'http://localhost:8888/apple.jpg',
                [
                    'width'     => 1200,
                    'height'    => 1200,
                    'type'      => IMAGETYPE_JPEG,
                    'file_size' => 224643,    
                ]
            ],
            'http://localhost:8888/beach.webp' => [
                false,
                'http://localhost:8888/beach.webp',
                [
                    'width'     => 730,
                    'height'    => 352,
                    'type'      => IMAGETYPE_WEBP,
                    'file_size' => 69622,
                ]
            ],
            'https://upload.wikimedia.org/wikipedia/en/a/a7/Culinary_fruits_cropped_top_view.jpg' => [
                false,
                'https://upload.wikimedia.org/wikipedia/en/a/a7/Culinary_fruits_cropped_top_view.jpg',
                [
                    'width'     => 3224,
                    'height'    => 2145,
                    'type'      => IMAGETYPE_JPEG,
                    'file_size' => 2925171,
                ]
            ],
            'https://pixnio.com/free-images/2020/01/24/2020-01-24-08-50-32-1200x800.jpg' => [
                false,
                'https://pixnio.com/free-images/2020/01/24/2020-01-24-08-50-32-1200x800.jpg',
                [
                    'width'     => 1200,
                    'height'    => 800,
                    'type'      => IMAGETYPE_JPEG,
                    'file_size' => 169395,
                ]
            ],
        ];        
    }
    
    public function wrongImagesProvider()
    {
        return [
            'http://localhost:8888/404.jpg' => [
                false,
                'http://localhost:8888/404.jpg',
                '/^Cannot get size of file.*/',
            ],
            'http://localhost:8888/not_image.jpg' => [
                false,
                'http://localhost:8888/not_image.jpg',
                '/^Cannot get size of image.*/',
            ],            
        ];
    }
}