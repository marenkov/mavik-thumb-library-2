<?php

namespace Mavik\Thumbnails;

class Exception extends \Exception {
    /** Codes of errors */
    const DIRECTORY_CREATION        = 1;
    const FILE_CREATION              = 2;
    const GRAPHIC_LIBRARY_IS_MISSING  = 3;
    const NOT_ENOUGH_MEMORY       = 4;
    const UNSUPPORTED_IMAGE_TYPE    = 5;
}
