<?php
namespace NeosRulez\AssetSource\GoogleCloudStorage\AssetSource;

use Neos\Flow\Annotations as Flow;
use NeosRulez\AssetSource\GoogleCloudStorage\Api\GoogleCloudStorageClient;
use Neos\Flow\ResourceManagement\ResourceManager;
use Neos\Media\Domain\Model\AssetSource\AssetProxyRepositoryInterface;
use Neos\Media\Domain\Model\AssetSource\AssetSourceInterface;

use Google\Cloud\Storage\StorageClient;

final class GoogleCloudStorageAssetSource implements AssetSourceInterface
{
    /**
     * @Flow\Inject
     * @var ResourceManager
     */
    protected $resourceManager;

    /**
     * @var string
     */
    private $assetSourceIdentifier;

    /**
     * @var GoogleCloudStorageAssetProxyRepository
     */
    private $assetProxyRepository;

    /**
     * @var GoogleCloudStorageClient
     */
    protected $googleCloudStorageClient;

    /**
     * @var string
     */
    private $defaultSearchTerm;

    /**
     * @var string
     */
    private $iconPath;

    /**
     * @var string
     */
    private $storageName;


    /**
     * GoogleCloudStorageAssetSource constructor.
     * @param string $assetSourceIdentifier
     * @param array $assetSourceOptions
     */
    public function __construct(string $assetSourceIdentifier, array $assetSourceOptions)
    {
        $this->assetSourceIdentifier = $assetSourceIdentifier;
        $this->googleCloudStorageClient = new GoogleCloudStorageClient(
            $assetSourceOptions['privateKeyJsonPathAndFilename'],
            $assetSourceOptions['bucketName'],
            $assetSourceOptions['signedUrlDuration']
        );
        $this->defaultSearchTerm = trim($assetSourceOptions['defaultSearchTerm']) ?? '';
        $this->iconPath = trim($assetSourceOptions['icon']) ?? '';
        $this->storageName = trim($assetSourceOptions['name']) ?? '';
    }

    /**
     * This factory method is used instead of a constructor in order to not dictate a __construct() signature in this
     * interface (which might conflict with an asset source's implementation or generated Flow proxy class).
     *
     * @param string $assetSourceIdentifier
     * @param array $assetSourceOptions
     * @return AssetSourceInterface
     */
    public static function createFromConfiguration(string $assetSourceIdentifier, array $assetSourceOptions): AssetSourceInterface
    {
        return new static($assetSourceIdentifier, $assetSourceOptions);
    }

    /**
     * A unique string which identifies the concrete asset source.
     * Must match /^[a-z][a-z0-9-]{0,62}[a-z]$/
     *
     * @return string
     */
    public function getIdentifier(): string
    {
        return $this->assetSourceIdentifier;
    }

    /**
     * @return string
     */
    public function getLabel(): string
    {
        return $this->storageName;
    }

    /**
     * @return AssetProxyRepositoryInterface
     */
    public function getAssetProxyRepository(): AssetProxyRepositoryInterface
    {
        if ($this->assetProxyRepository === null) {
            $this->assetProxyRepository = new GoogleCloudStorageAssetProxyRepository($this);
        }

        return $this->assetProxyRepository;
    }

    /**
     * @return GoogleCloudStorageClient
     */
    public function getGoogleCloudStorageClient(): GoogleCloudStorageClient
    {
        return $this->googleCloudStorageClient;
    }

    /**
     * @return string
     */
    public function getCopyRightNoticeTemplate(): string
    {
        return '';
    }

    /**
     * @return bool
     */
    public function isReadOnly(): bool
    {
        return true;
    }

    /**
     * @return string
     */
    public function getDefaultSearchTerm(): string
    {
        return $this->defaultSearchTerm;
    }

    /**
     * Returns the resource path to Assetsources icon
     *
     * @return string
     */
    public function getIconUri(): string
    {
        return $this->resourceManager->getPublicPackageResourceUriByPath($this->iconPath);
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return 'Files provided from Google Cloud Storage';
    }
}
