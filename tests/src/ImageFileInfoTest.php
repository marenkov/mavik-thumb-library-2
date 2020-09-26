<?php
namespace Mavik\Thumbnails;

use PHPUnit\Framework\TestCase;

class ImageFileInfoTest extends TestCase
{
    /**
     * @var ImageFileInfo
     */
    protected $imageFileInfo;

    protected function setUp(): void
    {
        $this->imageFileInfo = new ImageFileInfo(new Filesystem\Local([
            'webRootPath' => __DIR__ . '/../resources/images'
        ]));
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

        $result = $this->imageFileInfo->imageInfo($info);        
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
        ];        
    }
}
