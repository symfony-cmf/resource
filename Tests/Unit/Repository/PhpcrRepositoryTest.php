<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2014 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Component\DependencyInjection;

use Prophecy\PhpUnit\ProphecyTestCase;
use Symfony\Cmf\Component\Resource\Repository\PhpcrRepository;

class PhpcrRepositoryTest extends ProphecyTestCase
{
    public function setUp()
    {
        $this->session = $this->prophesize('PHPCR\SessionInterface');
        $this->finder = $this->prophesize('DTL\Glob\FinderInterface');
        $this->node = $this->prophesize('PHPCR\NodeInterface');

        $this->repository = new PhpcrRepository($this->session->reveal(), null, $this->finder->reveal());
    }

    public function testGet()
    {
        $this->session->getNode('/cmf/foobar')->willReturn($this->node);
        $this->node->getPath()->willReturn('/cmf/foobar');

        $res = $this->repository->get('/cmf/foobar');

        $this->assertInstanceOf('Symfony\Cmf\Component\Resource\PhpcrResource', $res);
        $this->assertEquals('/cmf', $res->getPath());
        $this->assertEquals('foobar', $res->getName());
        $this->assertSame($this->node->reveal(), $res->getNode());
    }

    public function testFind()
    {
        $this->session->getNode('/cmf/foobar')->willReturn($this->node);
        $this->finder->find('/cmf/*')->willReturn(array(
            $this->node
        ));

        $res = $this->repository->find('/cmf/*');

        $this->assertInstanceOf('Puli\Resource\Collection\ResourceCollection', $res);
        $this->assertCount(1, $res);
        $nodeResource = $res->offsetGet(0);
            ;
        $this->assertSame($this->node->reveal(), $nodeResource->getObject());
    }
}
