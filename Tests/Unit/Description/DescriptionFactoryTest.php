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
use Symfony\Cmf\Component\Resource\Description\DescriptionEnricherInterface;
use Prophecy\Argument;
use Symfony\Cmf\Component\Resource\Description\DescriptionFactory;
use Symfony\Cmf\Component\Resource\Repository\Resource\CmfResource;

class DescriptionFactoryTest extends \PHPUnit_Framework_TestCase
{
    private $factory;
    private $enricher1;
    private $enricher2;
    private $payload;
    private $resource;

    public function setUp()
    {
        $this->enricher1 = $this->prophesize(DescriptionEnricherInterface::class);
        $this->enricher2 = $this->prophesize(DescriptionEnricherInterface::class);
        $this->resource = $this->prophesize(CmfResource::class);

        $this->payload = new \stdClass();
        $this->resource->getPayload()->willReturn($this->payload);
        $this->resource->getPayloadType()->willReturn('payload-type');
    }

    /**
     * It should return an enriched description.
     */
    public function testGetPayloadDescription()
    {
        $this->enricher1->enrich(Argument::type(Description::class), $this->payload)
            ->will(function ($args) {
                $description = $args[0];
                $description->set('foobar', 'barfoo');
            });
        $this->enricher1->supports($this->resource->reveal())->willReturn(true);
        $this->enricher2->enrich(Argument::type(Description::class), $this->payload)
            ->will(function ($args) {
                $description = $args[0];
                $description->set('barfoo', 'foobar');
            });
        $this->enricher2->supports($this->resource->reveal())->willReturn(true);

        $description = $this->createFactory([
            $this->enricher1->reveal(),
            $this->enricher2->reveal(),
        ])->getPayloadDescriptionFor($this->resource->reveal());

        $this->assertInstanceOf(Description::class, $description);
        $this->assertEquals('barfoo', $description->get('foobar'));
        $this->assertEquals('foobar', $description->get('barfoo'));
    }

    /**
     * It should ignore providers that do not support the payload type.
     */
    public function testIgnoreNonSupporters()
    {
        $this->enricher1->enrich(Argument::cetera())->shouldNotBeCalled();
        $this->enricher1->supports($this->resource->reveal())->willReturn(false);

        $this->enricher2->enrich(Argument::cetera())->shouldBeCalled();
        $this->enricher2->supports($this->resource->reveal())->willReturn(true);

        $this->createFactory([
            $this->enricher1->reveal(),
            $this->enricher2->reveal(),
        ])->getPayloadDescriptionFor($this->resource->reveal());
    }

    /**
     * It should work when no enrichers are given.
     */
    public function testNoEnrichers()
    {
        $description = $this->createFactory([])->getPayloadDescriptionFor($this->resource->reveal());
        $this->assertInstanceOf(Description::class, $description);
    }

    private function createFactory(array $enrichers)
    {
        return new DescriptionFactory($enrichers);
    }
}
