<?php

namespace NeosRulez\AssetSource\GoogleCloudStorage\Api;

use NeosRulez\AssetSource\GoogleCloudStorage\Exception\ConfigurationException;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Neos\Cache\Frontend\VariableFrontend;

use Google\Cloud\Storage\StorageClient;

final class GoogleCloudStorageClient
{

    protected const QUERY_TYPE_CURATED = 'curated';
    protected const QUERY_TYPE_SEARCH = 'search';

    /**
     * @var string
     */
    private $privateKeyJsonPathAndFilename;

    /**
     * @var string
     */
    private $bucketName;

    /**
     * @var string
     */
    private $signedUrlDuration;

    /**
     * @var bool
     */
    private $renderImageThumbs;

    /**
     * @var StorageClient
     */
    private $client;

    /**
     * @var array
     */
    private $queryResults = [];

    /**
     * @var VariableFrontend
     */
    protected $filePropertyCache;


    /**
     * @param string $privateKeyJsonPathAndFilename
     * @param string $bucketName
     * @param string $signedUrlDuration
     * @param bool $renderImageThumbs
     */
    public function __construct(string $privateKeyJsonPathAndFilename, string $bucketName, string $signedUrlDuration, bool $renderImageThumbs)
    {
        $this->privateKeyJsonPathAndFilename = $privateKeyJsonPathAndFilename;
        $this->bucketName = $bucketName;
        $this->signedUrlDuration = $signedUrlDuration;
        $this->renderImageThumbs = $renderImageThumbs;
    }

    /**
     * @param int $pageSize
     * @param int $page
     * @return GoogleCloudStorageQueryResult
     * @throws GuzzleException
     * @throws \Neos\Cache\Exception
     */
    public function curated(int $pageSize = 20, int $page = 1): GoogleCloudStorageQueryResult
    {
        return $this->executeQuery(self::QUERY_TYPE_CURATED, $pageSize, $page);
    }

    /**
     * @param string $query
     * @param int $pageSize
     * @param int $page
     *
     * @return GoogleCloudStorageQueryResult
     * @throws GuzzleException
     * @throws \Neos\Cache\Exception
     */
    public function search(string $query, int $pageSize = 20, int $page = 1): GoogleCloudStorageQueryResult
    {
        return $this->executeQuery(self::QUERY_TYPE_SEARCH, $pageSize, $page, $query);
    }

    /**
     * @param string $identifier
     * @return mixed
     * @throws \Exception
     */
    public function findByIdentifier(string $identifier)
    {
        if (!$this->filePropertyCache->has($identifier)) {
            throw new \Exception(sprintf('file with id %s was not found in the cache', $identifier), 1525457755);
        }

        return $this->filePropertyCache->get($identifier);
    }

    /**
     * @param string $type
     * @param int $pageSize
     * @param int $page
     * @param string $query
     * @return GoogleCloudStorageQueryResult
     * @throws GuzzleException
     * @throws \Neos\Cache\Exception
     */
    private function executeQuery(string $type, int $pageSize = 20, int $page = 1, string $query = ''): GoogleCloudStorageQueryResult
    {

        $requestParameter = [
            'per_page' => $pageSize,
            'page' => $page
        ];

        if ($query !== '') {
            $requestParameter['query'] = $query;
        }

        $requestIdentifier = implode('_', $requestParameter);

        if (!isset($this->queryResults[$requestIdentifier])) {

            $bucket = $this->getClient();

            $startOffset = ($page - 1) * $pageSize;
            $endOffset = $pageSize;

            $objects = $bucket->bucket($this->bucketName)->objects();
            $results = [];
            $slicedObjects = [];
            $count = 0;
            foreach ($objects as $item) {
                $count = $count + 1;
                $slicedObjects[] = $item;
            }

            if($type == 'search') {
                $count = 0;
                foreach ($objects as $object) {
                    $name = strtolower($object->name());
                    $pos = strpos($name, strtolower($query));
                    if ($pos !== false) {
                        $results[] = $object;
                        $count = $count + 1;
                    }
                }
            }

            $objects = array_slice(!empty($results) ? $results : $slicedObjects, $startOffset, $endOffset);

            $files = [];
            foreach ($objects as $object) {
                if(array_key_exists('extension', pathinfo($object->name()))) {
                    $info = $object->info();
                    $ext = pathinfo($object->name())['extension'];
                    $preview = '/_Resources/Static/Packages/Neos.Media/IconSets/vivid/' . strtolower($ext) . '.svg';
                    if ($this->renderImageThumbs) {
                        if ($info['contentType'] == 'image/jpg' || $info['contentType'] == 'image/jpeg' || $info['contentType'] == 'image/png') {
                            $preview = $object->signedUrl(new \DateTime('+ ' . $this->signedUrlDuration . ' seconds'));
                        }
                    }
                    $item = [
                        'id' => md5($object->name()),
                        'url' => 'https://storage.googleapis.com/' . $this->bucketName . '/' . $object->name(),
                        'identifier' => $info['id'],
                        'size' => $info['size'],
                        'contentType' => $info['contentType'],
                        'mediaLink' => $info['mediaLink'],
                        'preview' => $preview,
                        'signedUrl' => $object->signedUrl(new \DateTime('+ ' . $this->signedUrlDuration . ' seconds'))
                    ];
                    if ($query == '') {
                        $files['files'][] = $item;
                    } else {
                        $pos = strpos(strtolower($object->name()), strtolower($query));
                        if ($pos === false) {

                        } else {
                            $files['files'][] = $item;
                        }
                    }
                }
            }

            $this->queryResults[$requestIdentifier] = $this->processResult($files, $count);
        }

        return $this->queryResults[$requestIdentifier];
    }

    /**
     * @param array $resultArray
     * @param int $totalResults
     * @return GoogleCloudStorageQueryResult
     * @throws \Neos\Cache\Exception
     */
    protected function processResult(array $resultArray, int $totalResults): GoogleCloudStorageQueryResult
    {
        $files = $resultArray['files'] ?? [];

        foreach ($files as $file) {
            if (isset($file['id'])) {
                $this->filePropertyCache->set((string)$file['id'], $file);
            }
        }

        return new GoogleCloudStorageQueryResult($files, $totalResults);
    }

    /**
     * @param string $url
     * @return false|resource
     */
    public function getFileStream(string $url)
    {

        $context = stream_context_create();

        $resource = fopen($url, 'r', false, $context);

        if (!is_resource($resource)) {
            throw new TransferException(sprintf('Unable to load an image from %s %s. Error: %s', $url, error_get_last()), 1600770625);
        }

        return $resource;
    }

    /**
     * @return StorageClient
     * @throws ConfigurationException
     */
    private function getClient(): StorageClient
    {
        if (trim($this->privateKeyJsonPathAndFilename) === '') {
            throw new ConfigurationException('No private key json file was defined. Add it to your settings', 1594199031);
        }

        if (trim($this->bucketName) === '') {
            throw new ConfigurationException('No bucket name was defined. Add it to your settings', 1594199031);
        }

        $this->client = new StorageClient([
            'keyFilePath' => $this->privateKeyJsonPathAndFilename
        ]);

        return $this->client;
    }
}
