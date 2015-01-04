<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2014 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Component\Resource\Finder;

use Prophecy\PhpUnit\ProphecyTestCase;
use Symfony\Cmf\Component\Resource\Factory\ContainerRepositoryFactory;

class ContainerRepositoryFactoryTest extends ProphecyTestCase
{
    public function setUp()
    {
        $this->container = $this->prophesize('Symfony\Component\DependencyInjection\ContainerInterface');
        $this->repository = $this->prophesize('Puli\Resource\RepositoryInterface');
    }

    private function getFactory($map)
    {
        $factory = new ContainerRepositoryFactory($this->container->reveal(), $map);

        return $factory;
    }

    public function testGet()
    {
        $factory = $this->getFactory(array('foobar' => 'foobar_id'));
        $this->container->get('foobar_id')->willReturn($this->repository->reveal());
        $res = $factory->create('foobar');

        $this->assertSame($this->repository->reveal(), $res);
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage No repository with name "foobar_notexist" has been registered
     */
    public function testGetNotDefined()
    {
        $factory = $this->getFactory(array('foobar' => 'foobar_id'));
        $this->container->get('foobar_id')->willReturn($this->repository->reveal());
        $res = $factory->create('foobar_notexist');

        $this->assertSame($this->repository->reveal(), $res);
    }
}
