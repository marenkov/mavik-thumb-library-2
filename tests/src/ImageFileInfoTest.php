<?php
namespace Mavik\Thumbnails;

use PHPUnit\Framework\TestCase;

class ImageFileInfoTest extends TestCase
{
    /**
     * @var ImageFileInfo
     */
    static protected $imageFileInfo;
    
    public static function setUpBeforeClass(): void
    {
        $webRoot = __DIR__ . '/../resources/images';
        self::startHttpServer($webRoot);
        sleep(1);
        self::$imageFileInfo = new ImageFileInfo(new Filesystem\Local([
            'webRootPath' => $webRoot,
        ]));
    }
    
    protected static function startHttpServer(string $webRoot): void
    {
        shell_exec("php -S localhost:8888 -t {$webRoot} > /dev/null 2>&1 &");
        $count = 0;
        do {
            usleep(10000);
            $content = @file_get_contents('http://localhost:8888');           
        } while (empty($content) && $count++ < 50);
        if (empty($content)) {
            throw new \Exception('HTTP server cannot be started');
        }
    }

    /**
     * @covers Mavik\Thumbnails\ImageFileInfo::imageInfo
     * @dataProvider imagesProvider
     */
    public function testImageInfo(bool $isLocal, string $src, $trueResult)
    {
        $info = new ImageInfo();
        $info->isLocal = $isLocal;
        if ($isLocal) {
            $info->path = $src;
        } else {
            $info->url = $src;
        }
        $result = self::$imageFileInfo->imageInfo($info);        
        $this->assertEquals($trueResult, $result);
    }
    
    public function imagesProvider()
    {
        return [
            [true, __DIR__ . '/../resources/images/apple.jpg', [
                'width'     => 1200,
                'height'    => 1200,
                'type'      => IMAGETYPE_JPEG,
                'file_size' => 224643,    
            ]],
            [true, __DIR__ . '/../resources/images/butterfly_with_transparent_bg.png', [
                'width'     => 1280,
                'height'    => 1201,
                'type'      => IMAGETYPE_PNG,
                'file_size' => 308897,    
            ]],
            [true, __DIR__ . '/../resources/images/chrismas_tree_with_transparent_bg.png', [
                'width'     => 1615,
                'height'    => 1920,
                'type'      => IMAGETYPE_PNG,
                'file_size' => 141327,    
            ]],
            [true, __DIR__ . '/../resources/images/pinapple-animated.gif', [
                'width'     => 457,
                'height'    => 480,
                'type'      => IMAGETYPE_GIF,
                'file_size' => 157012,
            ]],
            [true, __DIR__ . '/../resources/images/snowman-pixel.gif', [
                'width'     => 700,
                'height'    => 1300,
                'type'      => IMAGETYPE_GIF,
                'file_size' => 53777,    
            ]],
            [true, __DIR__ . '/../resources/images/tree_with_white_background.jpg', [
                'width'     => 1280,
                'height'    => 1280,
                'type'      => IMAGETYPE_JPEG,
                'file_size' => 181304,    
            ]],
            [true, __DIR__ . '/../resources/images/house.webp', [
                'width'     => 1536,
                'height'    => 1024,
                'type'      => IMAGETYPE_WEBP,
                'file_size' => 644986,
            ]],
            [true, __DIR__ . '/../resources/images/beach.webp', [
                'width'     => 730,
                'height'    => 352,
                'type'      => IMAGETYPE_WEBP,
                'file_size' => 69622,
            ]],
            [false, 'http://localhost:8888/apple.jpg', [
                'width'     => 1200,
                'height'    => 1200,
                'type'      => IMAGETYPE_JPEG,
                'file_size' => 224643,    
            ]],
            [false, 'http://localhost:8888/beach.webp', [
                'width'     => 730,
                'height'    => 352,
                'type'      => IMAGETYPE_WEBP,
                'file_size' => 69622,
            ]],
            [false, 'https://upload.wikimedia.org/wikipedia/en/a/a7/Culinary_fruits_cropped_top_view.jpg', [
                'width'     => 3224,
                'height'    => 2145,
                'type'      => IMAGETYPE_JPEG,
                'file_size' => 2925171,
            ]],
            [false, 'https://pixnio.com/free-images/2020/01/24/2020-01-24-08-50-32-1200x800.jpg', [
                'width'     => 1200,
                'height'    => 800,
                'type'      => IMAGETYPE_JPEG,
                'file_size' => 169395,
            ]],
        ];        
    }
}
