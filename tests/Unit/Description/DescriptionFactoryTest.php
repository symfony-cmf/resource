<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2017 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Component\Resource\Tests\Unit\Repository;

use Prophecy\Argument;
use Symfony\Cmf\Component\Resource\Description\Description;
use Symfony\Cmf\Component\Resource\Description\DescriptionEnhancerInterface;
use Symfony\Cmf\Component\Resource\Description\DescriptionFactory;
use Symfony\Cmf\Component\Resource\Puli\Api\PuliResource;

class DescriptionFactoryTest extends \PHPUnit_Framework_TestCase
{
    private $factory;
    private $enhancer1;
    private $enhancer2;
    private $resource;

    public function setUp()
    {
        $this->enhancer1 = $this->prophesize(DescriptionEnhancerInterface::class);
        $this->enhancer2 = $this->prophesize(DescriptionEnhancerInterface::class);
        $this->resource = $this->prophesize(PuliResource::class);
    }

    /**
     * It should return an enhanceed description.
     */
    public function testGetResourceDescription()
    {
        $this->enhancer1->enhance(Argument::type(Description::class))
            ->will(function ($args) {
                $description = $args[0];
                $description->set('foobar', 'barfoo');
            });
        $this->enhancer1->supports($this->resource->reveal())->willReturn(true);
        $this->enhancer2->enhance(Argument::type(Description::class))
            ->will(function ($args) {
                $description = $args[0];
                $description->set('barfoo', 'foobar');
            });
        $this->enhancer2->supports($this->resource->reveal())->willReturn(true);

        $description = $this->createFactory([
            $this->enhancer1->reveal(),
            $this->enhancer2->reveal(),
        ])->getPayloadDescriptionFor($this->resource->reveal());

        $this->assertInstanceOf(Description::class, $description);
        $this->assertEquals('barfoo', $description->get('foobar'));
        $this->assertEquals('foobar', $description->get('barfoo'));
    }

    /**
     * It should ignore providers that do not support the resource.
     */
    public function testIgnoreNonSupporters()
    {
        $this->enhancer1->enhance(Argument::cetera())->shouldNotBeCalled();
        $this->enhancer1->supports($this->resource->reveal())->willReturn(false);

        $this->enhancer2->enhance(Argument::cetera())->shouldBeCalled();
        $this->enhancer2->supports($this->resource->reveal())->willReturn(true);

        $this->createFactory([
            $this->enhancer1->reveal(),
            $this->enhancer2->reveal(),
        ])->getPayloadDescriptionFor($this->resource->reveal());
    }

    /**
     * It should work when no enhancers are provided.
     */
    public function testNoEnhancers()
    {
        $description = $this->createFactory([])->getPayloadDescriptionFor($this->resource->reveal());
        $this->assertInstanceOf(Description::class, $description);
    }

    private function createFactory(array $enhancers)
    {
        return new DescriptionFactory($enhancers);
    }
}
