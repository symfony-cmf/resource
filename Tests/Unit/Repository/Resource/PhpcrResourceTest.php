<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2015 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Component\Resource\Tests\Unit\Repository\Resource;

use Prophecy\PhpUnit\ProphecyTestCase;
use Symfony\Cmf\Component\Resource\Repository\Resource\PhpcrResource;

class PhpcrResourceTest extends ProphecyTestCase
{
    private $node;

    public function setUp()
    {
        parent::setUp();
        $this->node = $this->prophesize('PHPCR\NodeInterface');
        $this->childNode = $this->prophesize('PHPCR\NodeInterface');
        $this->resource = new PhpcrResource('/foo', $this->node->reveal());
    }

    public function testGetNode()
    {
        $this->assertSame($this->resource->getPayload(), $this->node->reveal());
    }

    public function testGetMetadata()
    {
        $res = $this->resource->getMetadata();
        $this->assertInstanceOf('Symfony\Cmf\Component\Resource\Repository\Resource\Metadata\PhpcrMetadata', $res);
    }
}
