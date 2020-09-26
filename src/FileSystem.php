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
 * Operations with file system
 */
interface FileSystem {
    public function __construct(array $params): void;
    public function realPath(string $path): ?string;
    public function pathToUrl(string $path): string;
    public function isDirectory(string $path): bool;
    public function isFile(string $path): bool;
    public function makeDirectory(string $path, int $mode = null): void;
    public function write(string $path, string $content, int $mode = null): void;
    public function read(string $path, int $maxLen = null): string;
    public function fileSize(string $path): int;
}
