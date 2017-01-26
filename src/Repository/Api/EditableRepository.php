<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2017 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Component\Resource\Repository\Api;

use Symfony\Cmf\Component\Resource\Puli\Api\ResourceRepository;

/**
 * Extends the Puli editable repository to implement the as-of-yet not
 * implemented features.
 *
 * @author Maximilian Berghoff <Maximilian.Berghoff@mayflower.de>
 *
 * @internal
 */
interface EditableRepository extends ResourceRepository
{
    /**
     * Adds a new resource to the repository.
     *
     * All resources passed to this method must implement {@link PuliResource}.
     *
     * @param string                          $path     the path at which to
     *                                                  add the resource
     * @param PuliResource|ResourceCollection $resource the resource(s) to add
     *                                                  at that path
     *
     * @throws \InvalidArgumentException if the path is invalid. The path must
     *                                   be a non-empty string starting with "/"
     * @throws \RuntimeException         if the resource is invalid
     */
    public function add($path, $resource);

    /**
     * Removes all resources matching the given query.
     *
     * @param string $query    a resource query
     * @param string $language The language of the query. All implementations
     *                         must support the language "glob".
     *
     * @return int the number of resources removed from the repository
     *
     * @throws \InvalidArgumentException if the query is invalid
     * @throws \RuntimeException         if the language is not supported
     */
    public function remove($query, $language = 'glob');

    /**
     * Removes all resources from the repository.
     *
     * @return int the number of resources removed from the repository
     */
    public function clear();

    /**
     * Move all resources and their subgraphs found by $sourceQuery to the
     * target (parent) path and returns the number of nodes that have been
     * *explicitly* moved (i.e. the number of resources found by the query, NOT
     * the total number of nodes affected).
     *
     * @param string $sourceQuery
     * @param string $targetPath
     * @param string $language
     *
     * @return int
     *
     * @throws \InvalidArgumentException if the sourceQuery is invalid
     * @throws \RuntimeException         if the language is not supported
     */
    public function move($sourceQuery, $targetPath, $language = 'glob');

    /**
     * Change the position of a node relative to its siblings.
     *
     * The $position is positive integer beginning at `0`. If an index is
     * specified greater than the number of siblings, then the node will be
     * added as the last sibling in the set, otherwise the node will be inserted
     * at the given $position.
     *
     * The $southPath identifies a single node.
     *
     * @param string $sourcePath
     * @param int    $position
     */
    public function reorder($sourcePath, $position);
}
