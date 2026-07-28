<?php

namespace WPDT\PhpOffice\PhpSpreadsheet\Writer;

use WPDT\ZipStream\Option\Archive;
use WPDT\ZipStream\ZipStream;
class ZipStream3
{
    /**
     * @param resource $fileHandle
     */
    public static function newZipStream($fileHandle) : ZipStream
    {
        return new ZipStream(enableZip64: \false, outputStream: $fileHandle, sendHttpHeaders: \false, defaultEnableZeroHeader: \false);
    }
}
