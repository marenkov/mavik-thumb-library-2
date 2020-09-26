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
     */
    public function testImageInfo()
    {
        $info = new ImageInfo();
        $info->isLocal = true;
        $info->path = __DIR__ . '/../resources/images/apple.jpg';
        $res = $this->imageFileInfo->imageInfo($info);
        
        $this->assertEquals([
            'width'     => 1200,
            'height'    => 1200,
            'type'      => IMG_JPEG,
            'file_size' => 224643,
        ], $res);
    }
}
