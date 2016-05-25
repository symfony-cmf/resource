<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2015 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Component\Resource\Description;

use Puli\Repository\Api\Resource\PuliResource;

/**
 * Descriptive metadata for resources.
 */
class Description
{
    /**
     * @var array
     */
    private $descriptors = [];

    /**
     * @var PuliResource
     */
    private $resource;

    /**
     * @param PuliResource $resource
     */
    public function __construct(PuliResource $resource)
    {
        $this->resource = $resource;
    }

    /**
     * Return the descriptors value for the given key.
     *
     * @param string $key
     *
     * @return mixed
     */
    public function get($key)
    {
        if (!isset($this->descriptors[$key])) {
            throw new \InvalidArgumentException(sprintf(
                'Descriptor "%s" not supported for resource "%s" of class "%s". Supported descriptors: "%s"',
                $key,
                $this->resource->getPath(),
                get_class($this->resource),
                implode('", "', array_keys($this->descriptors))
            ));
        }

        return $this->descriptors[$key];
    }

    /**
     * Set value for descriptors key.
     *
     * Note that:
     *
     * - It is possible to overwrite existing keys.
     *
     * - Where possible the key should be the value of one of the constants
     *   defined in the Descriptor class.
     *
     * @param string $key
     * @param mixed  $value
     */
    public function set($key, $value)
    {
        $this->descriptors[$key] = $value;
    }

    /**
     * Return the resource for which this is the description.
     *
     * @return PuliResource
     */
    public function getResource()
    {
        return $this->resource;
    }
}
