# Google Cloud Storage asset source

A package to use a private or public Google Cloud Storage as an asset source in Neos CMS.


## Installation

The NeosRulez.AssetSource.GoogleCloudStorage package is listed on packagist (https://packagist.org/packages/neosrulez/assetsource-googlecloudstorage) - therefore you don't have to include the package in your "repositories" entry any more.

Just run:

```
composer require neosrulez/assetsource-googlecloudstorage
```

## Configuration
Settings.yaml
```yaml
Neos:
  Media:
    assetSources:
      googleCloudStorage:
        assetSource: 'NeosRulez\AssetSource\GoogleCloudStorage\AssetSource\GoogleCloudStorageAssetSource'
        assetSourceOptions:
          privateKeyJsonPathAndFilename: '/data/neos/Data/Secrets/secure-project-135623-6b390b76f8bb.json'
          bucketName: 'your_bucketname'
          icon: 'resource://NeosRulez.AssetSource.GoogleCloudStorage/Public/GoogleG.svg'
          name: 'Cloud Storage'
          defaultSearchTerm: ''
          signedUrlDuration: '60'
```

## Author

* E-Mail: mail@patriceckhart.com
* URL: http://www.patriceckhart.com
