<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2015 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Component\Resource\Tests\Unit\Repository;

use Symfony\Cmf\Component\Resource\Description\Description;
use Symfony\Cmf\Component\Resource\Description\DescriptionEnhancerInterface;
use Prophecy\Argument;
use Symfony\Cmf\Component\Resource\Description\DescriptionFactory;
use Puli\Repository\Api\Resource\PuliResource;

class DescriptionFactoryTest extends \PHPUnit_Framework_TestCase
{
    private $factory;
    private $enhanceer1;
    private $enhanceer2;
    private $resource;

    public function setUp()
    {
        $this->enhanceer1 = $this->prophesize(DescriptionEnhancerInterface::class);
        $this->enhanceer2 = $this->prophesize(DescriptionEnhancerInterface::class);
        $this->resource = $this->prophesize(PuliResource::class);
    }

    /**
     * It should return an enhanceed description.
     */
    public function testGetResourceDescription()
    {
        $this->enhanceer1->enhance(Argument::type(Description::class))
            ->will(function ($args) {
                $description = $args[0];
                $description->set('foobar', 'barfoo');
            });
        $this->enhanceer1->supports($this->resource->reveal())->willReturn(true);
        $this->enhanceer2->enhance(Argument::type(Description::class))
            ->will(function ($args) {
                $description = $args[0];
                $description->set('barfoo', 'foobar');
            });
        $this->enhanceer2->supports($this->resource->reveal())->willReturn(true);

        $description = $this->createFactory([
            $this->enhanceer1->reveal(),
            $this->enhanceer2->reveal(),
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
        $this->enhanceer1->enhance(Argument::cetera())->shouldNotBeCalled();
        $this->enhanceer1->supports($this->resource->reveal())->willReturn(false);

        $this->enhanceer2->enhance(Argument::cetera())->shouldBeCalled();
        $this->enhanceer2->supports($this->resource->reveal())->willReturn(true);

        $this->createFactory([
            $this->enhanceer1->reveal(),
            $this->enhanceer2->reveal(),
        ])->getPayloadDescriptionFor($this->resource->reveal());
    }

    /**
     * It should work when no enhancers are given.
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
