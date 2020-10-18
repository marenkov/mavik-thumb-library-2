<?php
namespace Mavik\Thumbnails;

use PHPUnit\Framework\TestCase;

class ThumbPathTest extends TestCase
{        
    /** @var ThumbPath */
    private static $pathWithSubDirs;
    
    /** @var ThumbPath */
    private static $pathWithoutSubDirs;

    public static function setUpBeforeClass(): void
    {
        self::$pathWithSubDirs = new ThumbPath([
            'webDir'   => '/var/www/test.com',
            'thumbDir' => 'images/thmbnails',
            'subDirs'  => true,
        ]);
        self::$pathWithoutSubDirs = new ThumbPath([
            'webDir'   => '/var/www/test.com',
            'thumbDir' => 'images/thmbnails',
            'subDirs'  => false,
        ]);
    }

    /**
     * @covers Mavik\Thumbnails\ThumbPath::create
     * @dataProvider dataProvider
     */
    public function testCreateWithoutSubdirs(string $path, bool $isUrl, string $suffix, string $secondExt, array $expected)
    {
        $this->assertEquals(
            $expected['withoutSubDirs'],
            self::$pathWithoutSubDirs->create($path, $isUrl, $suffix, $secondExt)
        );
    }
    
    /**
     * @covers Mavik\Thumbnails\ThumbPath::create
     * @dataProvider dataProvider
     */
    public function testCreateWithSubdirs(string $path, bool $isUrl, string $suffix, string $secondExt, array $expected)
    {
        $this->assertEquals(
            $expected['withSubDirs'],
            self::$pathWithSubDirs->create($path, $isUrl, $suffix, $secondExt)
        );
    }
    
    public function dataProvider()
    {
        return [[
            'images/test.jpg', false, '-001', '', [
                'withoutSubDirs' => '/var/www/test.com/images/thmbnails/images-test-001.jpg',
                'withSubDirs' => '/var/www/test.com/images/thmbnails/images/test-001.jpg',
            ]], [
            'images/articles/test.jpg', false, '-002', '', [
                'withoutSubDirs' => '/var/www/test.com/images/thmbnails/images-articles-test-002.jpg',
                'withSubDirs' => '/var/www/test.com/images/thmbnails/images/articles/test-002.jpg',
            ]], [
            'images/test demo.jpg', false, '-003', '', [
                'withoutSubDirs' => '/var/www/test.com/images/thmbnails/images-test demo-003.jpg',
                'withSubDirs' => '/var/www/test.com/images/thmbnails/images/test demo-003.jpg',
            ]], [
            'images/../articles/test.jpg', false, '-004', '', [
                'withoutSubDirs' => '/var/www/test.com/images/thmbnails/images-..-articles-test-004.jpg',
                'withSubDirs' => '/var/www/test.com/images/thmbnails/articles/test-004.jpg',
            ]],
            
        ];
    }
}
