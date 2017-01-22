<?php

namespace Symfony\Cmf\Component\Resource\Puli\Api;

/**
 * Contains metadata about a resource.
 *
 * @since  1.0
 *
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class ResourceMetadata
{
    /**
     * Returns when the resource was created.
     *
     * If this information is not available, the method returns 0.
     *
     * @return int A UNIX timestamp.
     */
    public function getCreationTime()
    {
        return 0;
    }

    /**
     * Returns when the resource was last accessed.
     *
     * If this information is not available, the method returns 0.
     *
     * @return int A UNIX timestamp.
     */
    public function getAccessTime()
    {
        return 0;
    }

    /**
     * Returns when the resource was last modified.
     *
     * If this information is not available, the method returns 0.
     *
     * @return int A UNIX timestamp.
     */
    public function getModificationTime()
    {
        return 0;
    }

    /**
     * Returns the size of the body in bytes.
     *
     * If this information is not available, the method returns 0.
     *
     * @return int The body size in bytes.
     */
    public function getSize()
    {
        return 0;
    }
}
