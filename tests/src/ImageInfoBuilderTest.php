<?php
namespace Mavik\Thumbnails;

use PHPUnit\Framework\TestCase;

class ImageInfoBuilderTest extends TestCase
{        
    /**
     * @todo Тест выдает ложно-позитивный результат
     * 
     * @dataProvider dataProvider
     */
    public function testBuild(
        string $originalSrc,
        int $thumbWidth,
        int $thumbHeight,
        array $ratios,
        array $stubValues,
        array $result
    ) {
        $fileSystem = $this->createStub(FileSystem::class);
        $fileSystem->method('pathToUrl')->willReturn($stubValues['pathToUrl']);       
        $fileSystem->method('urlToPath')->willReturn($stubValues['urlToPath']);
        $fileSystem->method('realPath')->willReturn($stubValues['realPath']);

        $imageFileInformator = $this->createStub(ImageFileInformator::class);
        $imageFileInformator->method('imageInfo')->willReturn($stubValues['imageInfo']);

        $ImageInfoBuilder = new ImageInfoBuilder(
            [
                'baseUrl' => 'http://test.com/',
                'webDir'  => '/var/www/test.com/',
            ],
            $fileSystem,
            $imageFileInformator
        );
        $imageInfo = $ImageInfoBuilder->build($originalSrc, $thumbWidth, $thumbHeight, $ratios);
                
        $this->assertEquals($result, (array) $imageInfo);
    }

    public function dataProvider()
    {
        return [
            [
                'http://test.com/images/img.jpg',
                100,
                50,
                [1,2],
                [
                    'urlToPath' => '/var/www/test.com/images/img.jpg',
                    'pathToUrl' => 'http://test.com/images/thumbnails/img.jpg',
                    'realPath' => '/var/www/test.com/images/thumbnails/img.jpg',
                    'imageInfo' => [
                        'width'     => 400,
                        'height'    => 200,
                        'type'      => IMG_JPEG,
                        'file_size' => 50000,
                    ],
                ],
                [
                    'original' => [
                        'url' => 'http://test.com/images/img.jpg',
                        'path' => '/var/www/test.com/images/img.jpg',
                        'width'     => 400,
                        'height'    => 200,
                        'type'      => IMG_JPEG,
                        'isLocal'   => true,
                        'fileSize'  => 50000,
                    ],
                    'thumbnails' => [
                        [
                            'url' => 'http://test.com/images/img-100x50.jpg',
                            'path' => '/var/www/test.com/images/thumbnails/img-100x50.jpg',
                            'width'     => 100,
                            'height'    => 50,
                            'type'      => IMG_JPEG,
                            'isLocal'   => true,
                            'fileSize'  => 5000,
                        ], [
                            'url' => 'http://test.com/images/img-200x100.jpg',
                            'path' => '/var/www/test.com/images/thumbnails/img-200x100.jpg',
                            'width'     => 200,
                            'height'    => 100,
                            'type'      => IMG_JPEG,
                            'isLocal'   => true,
                            'fileSize'  => 5000,
                        ],
                    ],
                ]
            ]
        ];
    }
}
