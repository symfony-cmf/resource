<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2014 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Component\Resource;

/**
 * The registry is used to retrieve named repositories
 *
 * @author Daniel Leech <daniel@dantleech.com>
 */
interface RepositoryRegistryInterface
{
    /**
     * Return the named repository
     *
     * @param string $name
     *
     * @return Puli\Repository\Api\ResourceRepository
     */
    public function get($repositoryName);
}
