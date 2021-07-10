<?php

namespace NeosRulez\AssetSource\GoogleCloudStorage\AssetSource;

use DateTime;
use DateTimeInterface;
use NeosRulez\AssetSource\GoogleCloudStorage\Exception\TransferException;
use Neos\Eel\EelEvaluatorInterface;
use Neos\Eel\Exception;
use Neos\Eel\Utility;
use Neos\Flow\Annotations as Flow;
use Neos\Http\Factories\UriFactory;
use Neos\Media\Domain\Model\AssetSource\AssetProxy\AssetProxyInterface;
use Neos\Media\Domain\Model\AssetSource\AssetProxy\HasRemoteOriginalInterface;
use Neos\Media\Domain\Model\AssetSource\AssetProxy\ProvidesOriginalUriInterface;
use Neos\Media\Domain\Model\AssetSource\AssetProxy\SupportsIptcMetadataInterface;
use Neos\Media\Domain\Model\AssetSource\AssetSourceInterface;
use Neos\Media\Domain\Model\ImportedAsset;
use Neos\Media\Domain\Repository\ImportedAssetRepository;
use Psr\Http\Message\UriInterface;

final class GoogleCloudStorageAssetProxy implements AssetProxyInterface, HasRemoteOriginalInterface, SupportsIptcMetadataInterface, ProvidesOriginalUriInterface
{
    /**
     * @var array
     */
    private $file;

    /**
     * @var GoogleCloudStorageAssetSource
     */
    private $assetSource;

    /**
     * @var ImportedAsset
     */
    private $importedAsset;

    /**
     * @var array
     */
    private $iptcProperties;

    /**
     * @var array
     * @Flow\InjectConfiguration(path="defaultContext", package="Neos.Fusion")
     */
    protected $defaultContextConfiguration;

    /**
     * @Flow\Inject
     * @var UriFactory
     */
    protected $uriFactory;

    /**
     * @var EelEvaluatorInterface
     * @Flow\Inject(lazy=false)
     */
    protected $eelEvaluator;

    /**
     * @param array $file
     * @param GoogleCloudStorageAssetSource $assetSource
     */
    public function __construct(array $file, GoogleCloudStorageAssetSource $assetSource)
    {
        $this->file = $file;
        $this->assetSource = $assetSource;
        $this->importedAsset = (new ImportedAssetRepository)->findOneByAssetSourceIdentifierAndRemoteAssetIdentifier($assetSource->getIdentifier(), $this->getIdentifier());
    }

    /**
     * @return AssetSourceInterface
     */
    public function getAssetSource(): AssetSourceInterface
    {
        return $this->assetSource;
    }

    /**
     * @return string
     */
    public function getIdentifier(): string
    {
        return (string)$this->getProperty('id');
    }

    /**
     * @return string
     */
    public function getLabel(): string
    {
        $nameSlug = $this->extractSlugFromUrl();
        return $nameSlug !== '' ? str_replace('-', ' ', $nameSlug) : $this->getIdentifier();
    }

    /**
     * @return string
     */
    public function getFilename(): string
    {
        return basename($this->file['url']);
    }

    /**
     * @return string
     */
    protected function extractSlugFromUrl(): string
    {
        $url = $this->getProperty('url');

        if (!empty($url)) {
            $url = rtrim($url, '/');
            $urlParts = explode('/', $url);
            return trim(str_replace($this->getIdentifier(), '', end($urlParts)), '-');
        }

        return '';
    }

    /**
     * @return DateTimeInterface
     * @throws \Exception
     */
    public function getLastModified(): DateTimeInterface
    {
        return new DateTime();
    }

    public function getFileSize(): int
    {
        return $this->file['size'];
    }

    public function getMediaType(): string
    {
        return $this->file['contentType'];
    }

    /**
     * @return int|null
     */
    public function getWidthInPixels(): ?int
    {
        return (int)$this->getProperty('width');
    }

    public function getHeightInPixels(): ?int
    {
        return (int)$this->getProperty('height');
    }

    public function getThumbnailUri(): ?UriInterface
    {
        return $this->uriFactory->createUri($this->file['preview']);
    }

    public function getPreviewUri(): ?UriInterface
    {
        return $this->uriFactory->createUri($this->file['preview']);
    }

    public function getOriginalUri(): ?UriInterface
    {
        return $this->uriFactory->createUri($this->file['signedUrl']);
    }

    /**
     * @return resource
     * @throws TransferException
     */
    public function getImportStream()
    {
        return $this->assetSource->getGoogleCloudStorageClient()->getFileStream($this->getFileUrl());
    }

    /**
     * @return null|string
     */
    public function getLocalAssetIdentifier(): ?string
    {
        return $this->importedAsset instanceof ImportedAsset ? $this->importedAsset->getLocalAssetIdentifier() : '';
    }

    /**
     * Returns true if the binary data of the asset has already been imported into the Neos asset source.
     *
     * @return bool
     */
    public function isImported(): bool
    {
        return $this->importedAsset !== null;
    }

    /**
     * Returns true, if the given IPTC metadata property is available, ie. is supported and is not empty.
     *
     * @param string $propertyName
     * @return bool
     * @throws Exception
     */
    public function hasIptcProperty(string $propertyName): bool
    {
        return isset($this->getIptcProperties()[$propertyName]);
    }

    /**
     * Returns the given IPTC metadata property if it exists, or an empty string otherwise.
     *
     * @param string $propertyName
     * @return string
     * @throws Exception
     */
    public function getIptcProperty(string $propertyName): string
    {
        return $this->getIptcProperties()[$propertyName] ?? '';
    }

    /**
     * Returns all known IPTC metadata properties as key => value (e.g. "Title" => "My File")
     *
     * @return array
     * @throws Exception
     */
    public function getIptcProperties(): array
    {
        if ($this->iptcProperties === null) {
            $this->iptcProperties = [
                'Title' => $this->getLabel(),
                'CopyrightNotice' => $this->compileCopyrightNotice(['name' => $this->getProperty('filegrapher')]),
            ];
        }

        return $this->iptcProperties;
    }

    /**
     * @param string $propertyName
     * @return mixed|null
     */
    protected function getProperty(string $propertyName)
    {
        return $this->file[$propertyName] ?? null;
    }

    /**
     *
     * @return string
     */
    protected function getFileUrl(): string
    {
        return $this->file['signedUrl'];
    }

    /**
     * @param array $userProperties
     * @return string
     * @throws Exception
     */
    protected function compileCopyrightNotice(array $userProperties): string
    {
        return '';
    }
}
