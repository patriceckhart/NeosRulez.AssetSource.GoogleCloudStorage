<?php

namespace NeosRulez\AssetSource\GoogleCloudStorage\Api;

class GoogleCloudStorageQueryResult
{

    /**
     * @var \ArrayObject
     */
    protected $files = [];

    /**
     * @var \ArrayIterator
     */
    protected $fileIterator;

    /**
     * @var int
     */
    protected $totalResults = 30;

    /**
     * @param array $files
     * @param int $totalResults
     */
    public function __construct(array $files, int $totalResults)
    {
        $this->files = new \ArrayObject($files);
        $this->fileIterator = $this->files->getIterator();
        $this->totalResults = $totalResults;
    }

    /**
     * @return \ArrayObject
     */
    public function getFiles(): \ArrayObject
    {
        return $this->files;
    }

    /**
     * @return \ArrayIterator
     */
    public function getFileIterator(): \ArrayIterator
    {
        return $this->fileIterator;
    }

    /**
     * @return int
     */
    public function getTotalResults(): int
    {
        return $this->totalResults;
    }
}
