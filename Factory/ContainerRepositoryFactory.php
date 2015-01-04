<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2014 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Component\Resource\Factory;

use Symfony\Cmf\Component\Resource\RepositoryFactoryInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Factory which uses a Symfony DI container to create
 * new repositories.
 *
 * NOTE: All repository services should be defined as scope=prototype
 *       otherwise the same repository will be returned each time. This
 *       is almost certainly not what you want.
 *
 * @author Daniel Leech <daniel@dantleech.com>
 */
class ContainerRepositoryFactory implements RepositoryFactoryInterface
{
    /**
     * @var array
     */
    private $repositoryServiceMap = array();

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @param array repositoryServiceMap
     */
    public function __construct(ContainerInterface $container, $repositoryServiceMap = array())
    {
        $this->repositoryServiceMap = $repositoryServiceMap;
        $this->container = $container;
    }

    /**
     * Return a new instance of the named repository
     *
     * @param string $name
     *
     * @return Puli\Resource\RepositoryInterface
     */
    public function create($name)
    {
        return $this->container->get($this->getRepositoryServiceId($name));
    }

    private function getRepositoryServiceId($name)
    {
        if (!isset($this->repositoryServiceMap[$name])) {
            throw new \InvalidArgumentException(sprintf(
                'No repository with name "%s" has been registered',
                $name
            ));
        }

        return $this->repositoryServiceMap[$name];
    }
}
