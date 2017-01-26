<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2017 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Component\Resource\Description;

use Symfony\Cmf\Component\Resource\Puli\Api\PuliResource;

/**
 * Descriptive metadata for resources.
 *
 * @internal
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
     * Return the descriptors value for the given descriptor.
     *
     * @param string $descriptor
     *
     * @return mixed
     */
    public function get($descriptor)
    {
        if (!isset($this->descriptors[$descriptor])) {
            throw new \InvalidArgumentException(sprintf(
                'Descriptor "%s" not supported for resource "%s" of class "%s". Supported descriptors: "%s"',
                $descriptor,
                $this->resource->getPath(),
                get_class($this->resource),
                implode('", "', array_keys($this->descriptors))
            ));
        }

        return $this->descriptors[$descriptor];
    }

    /**
     * Return true if the given descriptor has been set.
     *
     * @param string $descriptor
     *
     * @return bool
     */
    public function has($descriptor)
    {
        return isset($this->descriptors[$descriptor]);
    }

    /**
     * Return all of the descriptors.
     *
     * @return array
     */
    public function all()
    {
        return $this->descriptors;
    }

    /**
     * Set value for descriptors descriptor.
     *
     * Note that:
     *
     * - It is possible to overwrite existing descriptors.
     *
     * - Where possible the descriptor should be the value of one of the constants
     *   defined in the Descriptor class.
     *
     * @param string $descriptor
     * @param mixed  $value
     */
    public function set($descriptor, $value)
    {
        if (null !== $value && !is_scalar($value) && !is_array($value)) {
            throw new \InvalidArgumentException(sprintf(
                'Only scalar and array values are allowed as descriptor values, got "%s" when setting descriptor "%s"',
                gettype($value), $descriptor
            ));
        }

        $this->descriptors[$descriptor] = $value;
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
