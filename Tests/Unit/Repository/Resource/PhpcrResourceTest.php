<?php

namespace Symfony\Cmf\Component\Resource\Tests\Unit\Repository\Resource;

use Prophecy\PhpUnit\ProphecyTestCase;
use Symfony\Cmf\Component\Resource\Repository\Resource\PhpcrResource;

class PhpcrResourceTest extends ProphecyTestCase
{
    private $node;

    public function setUp()
    {
        $this->node = $this->prophesize('PHPCR\NodeInterface');
        $this->childNode = $this->prophesize('PHPCR\NodeInterface');
        $this->resource = new PhpcrResource('/foo', $this->node->reveal());
    }

    public function testGetNode()
    {
        $this->assertSame($this->resource->getNode(), $this->node->reveal());
    }

    public function testGetName()
    {
        $this->node->getName()->willReturn('foo');
        $this->assertEquals('foo', $this->resource->getName());
    }

    public function testGetChild()
    {
        $this->node->getNode('foo')->willReturn($this->childNode->reveal());
        $res = $this->resource->getChild('foo');
        $this->assertInstanceOf('Symfony\Cmf\Component\Resource\Repository\Resource\PhpcrResource', $res);
        $this->assertSame($res->getNode(), $this->childNode->reveal());
    }

    public function testHasChild()
    {
        $this->node->hasNode('foo')->willReturn(true);
        $this->node->hasNode('bar')->willReturn(false);
        $this->assertTrue($this->resource->hasChild('foo'));
        $this->assertFalse($this->resource->hasChild('bar'));
    }

    public function testHasChildren()
    {
        $this->node->hasNodes()->willReturn(true);
        $res = $this->resource->hasChildren();
        $this->assertTrue($res);
    }

    public function testListChildren()
    {
        $this->node->getNodes()->willReturn(array(
            $this->childNode
        ));
        $res = $this->resource->listChildren();
        $this->assertInstanceOf('Puli\Repository\Resource\Collection\ArrayResourceCollection', $res);
    }

    public function testGetMetadata()
    {
        $res = $this->resource->getMetadata();
        $this->assertInstanceOf('Symfony\Cmf\Component\Resource\Repository\Resource\Metadata\PhpcrMetadata', $res);
    }
}
