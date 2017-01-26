<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2017 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Component\Resource;

use Symfony\Cmf\Component\Resource\Puli\Api\ResourceRepository;

/**
 * The registry is used to retrieve named repositories.
 *
 * @author Daniel Leech <daniel@dantleech.com>
 *
 * @internal
 */
interface RepositoryRegistryInterface
{
    /**
     * Return the names of all the registered repositories.
     *
     * @return string[]
     */
    public function names();

    /**
     * Return all repositories.
     *
     * Keys must be the repository names.
     *
     * @return ResourceRepository[]
     */
    public function all();

    /**
     * Return the named repository.
     *
     * @param null|string $name
     *
     * @return ResourceRepository
     */
    public function get($repositoryName = null);

    /**
     * Return the name assigned to the given resource repository.
     *
     * @return string
     *
     * @throws \RuntimeException If the name cannot be determined
     */
    public function getRepositoryName(ResourceRepository $resource);

    /**
     * Return the type for the given resource repository.
     *
     * @return string
     *
     * @throws \RuntimeException If the resource repository is not mapped
     */
    public function getRepositoryType(ResourceRepository $resource);
}
