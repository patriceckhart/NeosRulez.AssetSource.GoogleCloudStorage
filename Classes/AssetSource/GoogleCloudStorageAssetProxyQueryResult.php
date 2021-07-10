<?php

namespace NeosRulez\AssetSource\GoogleCloudStorage\AssetSource;

use NeosRulez\AssetSource\GoogleCloudStorage\Api\googleCloudStorageQueryResult;
use Neos\Media\Domain\Model\AssetSource\AssetProxy\AssetProxyInterface;
use Neos\Media\Domain\Model\AssetSource\AssetProxyQueryInterface;
use Neos\Media\Domain\Model\AssetSource\AssetProxyQueryResultInterface;

final class GoogleCloudStorageAssetProxyQueryResult implements AssetProxyQueryResultInterface
{
    /**
     * @var GoogleCloudStorageAssetSource
     */
    private $assetSource;

    /**
     * @var googleCloudStorageQueryResult
     */
    private $googleCloudStorageQueryResult = [];

    /**
     * @var GoogleCloudStorageAssetProxyQuery
     */
    private $GoogleCloudStorageAssetProxyQuery;

    /**
     * @param GoogleCloudStorageAssetProxyQuery $query
     * @param googleCloudStorageQueryResult $googleCloudStorageQueryResult
     * @param GoogleCloudStorageAssetSource $assetSource
     */
    public function __construct(GoogleCloudStorageAssetProxyQuery $query, googleCloudStorageQueryResult $googleCloudStorageQueryResult, GoogleCloudStorageAssetSource $assetSource)
    {
        $this->GoogleCloudStorageAssetProxyQuery = $query;
        $this->assetSource = $assetSource;
        $this->googleCloudStorageQueryResult = $googleCloudStorageQueryResult;
    }

    /**
     * Returns a clone of the query object
     *
     * @return AssetProxyQueryInterface
     */
    public function getQuery(): AssetProxyQueryInterface
    {
        return clone $this->GoogleCloudStorageAssetProxyQuery;
    }

    /**
     * Returns the first asset proxy in the result set
     *
     * @return AssetProxyInterface|null
     */
    public function getFirst(): ?AssetProxyInterface
    {
        return $this->offsetGet(0);
    }

    /**
     * Returns an array with the asset proxies in the result set
     *
     * @return AssetProxyInterface[]
     * @throws \Exception
     */
    public function toArray(): array
    {
        return $this->googleCloudStorageQueryResult->getfiles()->getArrayCopy();
    }

    /**
     * Return the current element
     * @link http://php.net/manual/en/iterator.current.php
     * @return mixed Can return any type.
     * @since 5.0.0
     */
    public function current()
    {
        $file = $this->googleCloudStorageQueryResult->getfileIterator()->current();

        if(is_array($file)) {
            return new GoogleCloudStorageAssetProxy($file, $this->assetSource);
        } else {
            return null;
        }
    }

    /**
     * Move forward to next element
     * @link http://php.net/manual/en/iterator.next.php
     * @return void Any returned value is ignored.
     * @since 5.0.0
     */
    public function next()
    {
        $this->googleCloudStorageQueryResult->getfileIterator()->next();
    }

    /**
     * Return the key of the current element
     * @link http://php.net/manual/en/iterator.key.php
     * @return mixed scalar on success, or null on failure.
     * @since 5.0.0
     */
    public function key()
    {
        return $this->googleCloudStorageQueryResult->getfileIterator()->key();
    }

    /**
     * Checks if current position is valid
     * @link http://php.net/manual/en/iterator.valid.php
     * @return bool The return value will be casted to boolean and then evaluated.
     * Returns true on success or false on failure.
     * @since 5.0.0
     */
    public function valid()
    {
        return $this->googleCloudStorageQueryResult->getfileIterator()->valid();
    }

    /**
     * Rewind the Iterator to the first element
     * @link http://php.net/manual/en/iterator.rewind.php
     * @return void Any returned value is ignored.
     * @since 5.0.0
     */
    public function rewind()
    {
        $this->googleCloudStorageQueryResult->getfileIterator()->rewind();
    }

    /**
     * Whether a offset exists
     * @link http://php.net/manual/en/arrayaccess.offsetexists.php
     * @param mixed $offset <p>
     * An offset to check for.
     * </p>
     * @return boolean true on success or false on failure.
     * </p>
     * <p>
     * The return value will be casted to boolean if non-boolean was returned.
     * @since 5.0.0
     */
    public function offsetExists($offset)
    {
        return $this->googleCloudStorageQueryResult->getfileIterator()->offsetExists($offset);
    }

    /**
     * Offset to retrieve
     * @link http://php.net/manual/en/arrayaccess.offsetget.php
     * @param mixed $offset <p>
     * The offset to retrieve.
     * </p>
     * @return mixed Can return all value types.
     * @since 5.0.0
     */
    public function offsetGet($offset)
    {
        return new GoogleCloudStorageAssetProxy($this->googleCloudStorageQueryResult->getfileIterator()->offsetGet($offset), $this->assetSource);
    }

    /**
     * Offset to set
     * @link http://php.net/manual/en/arrayaccess.offsetset.php
     * @param mixed $offset <p>
     * The offset to assign the value to.
     * </p>
     * @param mixed $value <p>
     * The value to set.
     * </p>
     * @return void
     * @since 5.0.0
     */
    public function offsetSet($offset, $value)
    {
        $this->googleCloudStorageQueryResult->getfileIterator()->offsetSet($offset, $value);
    }

    /**
     * Offset to unset
     * @link http://php.net/manual/en/arrayaccess.offsetunset.php
     * @param mixed $offset <p>
     * The offset to unset.
     * </p>
     * @return void
     * @since 5.0.0
     * @throws \Exception
     */
    public function offsetUnset($offset)
    {
        $this->googleCloudStorageQueryResult->getfileIterator()->offsetUnset($offset);
    }

    /**
     * Count elements of an object
     * @link http://php.net/manual/en/countable.count.php
     * @return int The custom count as an integer.
     * </p>
     * <p>
     * The return value is cast to an integer.
     * @since 5.1.0
     */
    public function count()
    {
        return $this->googleCloudStorageQueryResult->getTotalResults();
    }
}
