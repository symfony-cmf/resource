<?php

declare(strict_types=1);

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Component\Resource\Puli\Api;

/**
 * Stores {@link PuliResource} objects.
 *
 * A resource repository is similar to a filesystem. It stores {@link PuliResource}
 * objects, each of which has a path in the repository:
 *
 * ```php
 * $resource = $repo->get('/css/style.css');
 * ```
 *
 * Resources may have child resources. These can be accessed with
 * {@link listChildren()}:
 *
 * ```php
 * $resource = $repo->get('/css');
 *
 * foreach ($resource->listChildren() as $name => $resource) {
 *     // ...
 * }
 * ```
 *
 * @since  1.0
 *
 * @author Bernhard Schussek <bschussek@gmail.com>
 * @author Titouan Galopin <galopintitouan@gmail.com>
 */
interface ResourceRepository
{
    /**
     * Returns the resource at the given path.
     *
     * @param string $path The path to the resource. Must start with "/". "."
     *                     and ".." segments in the path are supported.
     *
     * @throws \RuntimeException         if the resource cannot be found
     * @throws \InvalidArgumentException if the path is invalid. The path must
     *                                   be a non-empty string starting with "/"
     *
     * @return PuliResource the resource at this path
     */
    public function get($path);

    /**
     * Returns the resources matching a query.
     *
     * @param string $query    a resource query
     * @param string $language The language of the query. All implementations
     *                         must support the language "glob".
     *
     * @throws \InvalidArgumentException    if the query is invalid
     * @throws UnsupportedLanguageException if the language is not supported
     *
     * @return ResourceCollection the resources matching the query
     */
    public function find($query, $language = 'glob');

    /**
     * Returns whether any resources match a query.
     *
     * @param string $query    a resource query
     * @param string $language The language of the query. All implementations
     *                         must support the language "glob".
     *
     * @throws \InvalidArgumentException    if the query is invalid
     * @throws UnsupportedLanguageException if the language is not supported
     *
     * @return bool returns `true` if any resources exist that match the query
     */
    public function contains($query, $language = 'glob');

    /**
     * Returns whether a resource has child resources.
     *
     * @param string $path The path to the resource. Must start with "/".
     *                     "." and ".." segments in the path are supported.
     *
     * @throws \RuntimeException         if the resource cannot be found
     * @throws \InvalidArgumentException If the path is invalid. The path must
     *                                   be a non-empty string starting with "/".
     *
     * @return bool returns `true` if the resource has child resources
     */
    public function hasChildren($path);

    /**
     * Lists the child resources of a resource.
     *
     * @param string $path The path to the resource. Must start with "/".
     *                     "." and ".." segments in the path are supported.
     *
     * @throws \RuntimeException         if the resource cannot be found
     * @throws \InvalidArgumentException If the path is invalid. The path must
     *                                   be a non-empty string starting with "/".
     *
     * @return ResourceCollection the child resources of the resource
     */
    public function listChildren($path);
}
