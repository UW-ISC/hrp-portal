<?php

namespace WPDT\PhpOffice\PhpSpreadsheet\Writer;

use WPDT\ZipStream\Option\Archive;
use WPDT\ZipStream\ZipStream;
class ZipStream0
{
    /**
     * @param resource $fileHandle
     */
    public static function newZipStream($fileHandle) : ZipStream
    {
        return \class_exists(Archive::class) ? ZipStream2::newZipStream($fileHandle) : ZipStream3::newZipStream($fileHandle);
    }
}
