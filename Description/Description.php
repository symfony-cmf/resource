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

class Description
{
    private $descriptors = [];
    private $payloadType;

    public function __construct($payloadType)
    {
        $this->payloadType = $payloadType;
    }

    /**
     * Return the descriptors value for the given key.
     *
     * The key should be one of the constants defined in this class.
     *
     * @param string $key
     *
     * @return mixed
     */
    public function get($key)
    {
        if (!isset($this->descriptors[$key])) {
            throw new \InvalidArgumentException(sprintf(
                'Descriptor "%s" not supported for payload type "%s". Supported descriptors: "%s"',
                $key,
                $this->payloadType,
                implode('", "', array_keys($this->descriptors))
            ));
        }

        return $this->descriptors[$key];
    }

    /**
     * Set value for descriptors key.
     *
     * - It is possible to overwrite existing keys.
     *
     * - To help ensure interoperability, where possible, the key should be the
     *   value of one of the appropriate constants defined in the MetadescriptorsKey
     *   class.
     *
     * @param string $key
     * @param mixed  $value
     */
    public function set($key, $value)
    {
        $this->descriptors[$key] = $value;
    }
}
