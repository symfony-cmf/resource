<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2015 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Component\Resource;

use Puli\Repository\Api\ResourceRepository;

/**
 * The registry is used to retrieve named repositories.
 *
 * @author Daniel Leech <daniel@dantleech.com>
 */
interface RepositoryFactoryInterface
{
    /**
     * Create a new repository instance using the given configuration.
     *
     * @return ResourceRepository
     */
    public function create(array $options);

    /**
     * Return the default configuration for this factory.
     * Default values may be null.
     *
     * @return array
     */
    public function getDefaultConfig();
}
