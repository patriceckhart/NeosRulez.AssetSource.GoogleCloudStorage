<?php
namespace NeosRulez\AssetSource\GoogleCloudStorage\AssetSource;

use GuzzleHttp\Exception\GuzzleException;
use Neos\Media\Domain\Model\AssetSource\AssetNotFoundExceptionInterface;
use Neos\Media\Domain\Model\AssetSource\AssetProxy\AssetProxyInterface;
use Neos\Media\Domain\Model\AssetSource\AssetProxyQueryResultInterface;
use Neos\Media\Domain\Model\AssetSource\AssetProxyRepositoryInterface;
use Neos\Media\Domain\Model\AssetSource\AssetSourceConnectionExceptionInterface;
use Neos\Media\Domain\Model\AssetSource\AssetTypeFilter;
use Neos\Media\Domain\Model\Tag;

final class GoogleCloudStorageAssetProxyRepository implements AssetProxyRepositoryInterface
{
    /**
     * @var GoogleCloudStorageAssetSource
     */
    private $assetSource;

    /**
     * @param GoogleCloudStorageAssetSource $assetSource
     */
    public function __construct(GoogleCloudStorageAssetSource $assetSource)
    {
        $this->assetSource = $assetSource;
    }

    /**
     * @param string $identifier
     * @return AssetProxyInterface
     * @throws AssetNotFoundExceptionInterface
     * @throws AssetSourceConnectionExceptionInterface
     * @throws \Exception
     */
    public function getAssetProxy(string $identifier): AssetProxyInterface
    {
        return new GoogleCloudStorageAssetProxy($this->assetSource->getGoogleCloudStorageClient()->findByIdentifier($identifier), $this->assetSource);
    }

    /**
     * @param AssetTypeFilter $assetType
     */
    public function filterByType(AssetTypeFilter $assetType = null): void
    {
    }

    /**
     * @return AssetProxyQueryResultInterface
     * @throws AssetSourceConnectionExceptionInterface
     * @throws \Exception
     * @throws GuzzleException
     */
    public function findAll(): AssetProxyQueryResultInterface
    {
        $query = new GoogleCloudStorageAssetProxyQuery($this->assetSource);
        return $query->execute();
    }

    /**
     * @param string $searchTerm
     * @return AssetProxyQueryResultInterface
     * @throws AssetSourceConnectionExceptionInterface
     * @throws \Exception
     * @throws GuzzleException
     */
    public function findBySearchTerm(string $searchTerm): AssetProxyQueryResultInterface
    {
        $query = new GoogleCloudStorageAssetProxyQuery($this->assetSource);
        $query->setSearchTerm($searchTerm);
        return $query->execute();
    }

    /**
     * @param Tag $tag
     * @return AssetProxyQueryResultInterface
     * @throws \Exception
     * @throws GuzzleException
     */
    public function findByTag(Tag $tag): AssetProxyQueryResultInterface
    {
        return $this->findAll();
    }

    /**
     * @return AssetProxyQueryResultInterface
     * @throws \Exception
     * @throws GuzzleException
     */
    public function findUntagged(): AssetProxyQueryResultInterface
    {
        return $this->findAll();
    }

    /**
     * Count all assets, regardless of tag or collection
     *
     * @return int
     */
    public function countAll(): int
    {
        return 40000;
    }
}
