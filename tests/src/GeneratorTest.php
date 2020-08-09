<?php

namespace Mavik\Thumbnails;

use PHPUnit\Framework\TestCase;

class GeneratorTest extends TestCase {

    /**
     * @covers Mavik\Thumbnails\Generator::__construct
     */
    public function testConstructor() {
        $generator = new Generator();
        $this->assertInstanceOf('Mavik\Thumbnails\Generator', $generator);
    }
}
