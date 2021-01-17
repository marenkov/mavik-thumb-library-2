<?php
namespace Mavik\Thumbnails\Tests;

class HttpServer
{
    public static function start(string $webRoot): void
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
}