<?php

namespace Mavik\Thumbnails;

class Exception extends \Exception {
    /** Codes of errors */
    const CONFIGURATION             =1;
    const DIRECTORY_CREATION        = 2;
    const FILE_CREATION              = 3;
    const FILE_SET_MODE             = 4;
    const GRAPHIC_LIBRARY_IS_MISSING  = 5;
    const NOT_ENOUGH_MEMORY       = 6;
    const UNSUPPORTED_IMAGE_TYPE    = 7;
}
