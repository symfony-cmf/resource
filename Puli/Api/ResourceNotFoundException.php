<?php

namespace Symfony\Cmf\Component\Resource\Puli\Api;

/**
 * Thrown when a requested resource was not found.
 *
 * @since  1.0
 *
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class ResourceNotFoundException extends \RuntimeException
{
    /**
     * Creates a new exception for a resource path.
     *
     * @param string          $path  The path which was not found.
     * @param \Exception|null $cause The exception that caused this exception.
     *
     * @return static The created exception.
     */
    public static function forPath($path, \Exception $cause = null)
    {
        return new static(, 0, $cause);
    }
}
