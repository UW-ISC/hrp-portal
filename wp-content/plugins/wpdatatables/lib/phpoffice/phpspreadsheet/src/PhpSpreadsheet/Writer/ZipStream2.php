<?php

namespace WPDT\PhpOffice\PhpSpreadsheet\Writer;

use WPDT\ZipStream\Option\Archive;
use WPDT\ZipStream\ZipStream;
class ZipStream2
{
    /**
     * @param resource $fileHandle
     */
    public static function newZipStream($fileHandle) : ZipStream
    {
        $options = new Archive();
        $options->setEnableZip64(\false);
        $options->setOutputStream($fileHandle);
        return new ZipStream(null, $options);
    }
}
