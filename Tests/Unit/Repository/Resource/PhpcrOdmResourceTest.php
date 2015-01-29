<?php

namespace Symfony\Cmf\Component\Resource\Tests\Unit\Repository\Resource;

use Prophecy\PhpUnit\ProphecyTestCase;
use Symfony\Cmf\Component\Resource\Repository\Resource\PhpcrOdmResource;

class PhpcrOdmResourceTest extends ProphecyTestCase
{
    private $document;

    public function setUp()
    {
        $this->document = new \stdClass();
        $this->resource = new PhpcrOdmResource('/foo/foo:bar', $this->document);
    }

    public function testGetDocument()
    {
        $this->assertSame($this->resource->getPayload(), $this->document);
    }

    public function testGetName()
    {
        $this->assertEquals('foo:bar', $this->resource->getName());
    }
}
