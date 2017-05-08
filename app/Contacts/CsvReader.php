<?php

namespace RollCall\Contacts;

use RollCall\Contracts\Contacts\CsvReader as CsvReaderInterface;
use League\Csv\Reader;
use Illuminate\Support\Facades\Storage;

class CsvReader implements CsvReaderInterface
{
    /**
     * The CSV reader
     *
     * @var object
     */
    private $reader;

    public function __construct($file_path)
    {
        // Create reader from path.
        // The assumption here is that we either store
        // the file locally or on the cloud.
        if (config('filesystems.default') == 'local') {
            $file_path = storage_path() .'/app/'. $file_path;
            $this->reader = Reader::createFromPath($file_path, 'r');
        } else {
            // Create a reader from file contents retrieved from cloud storage
            $contents = Storage::get($file_path);
            $this->reader = Reader::createFromString($contents);
        }
    }

    public function read()
    {
        // Read the file and ignore the header
        return $this->reader->setOffset(1)->fetchAll();
    }

    public function fetchHeader()
    {
        return $this->reader->fetchOne();
    }
}
