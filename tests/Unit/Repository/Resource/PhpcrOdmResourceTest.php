<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2017 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Component\Resource\Tests\Unit\Repository\Resource;

use Symfony\Cmf\Component\Resource\Repository\Resource\PhpcrOdmResource;

class PhpcrOdmResourceTest extends \PHPUnit_Framework_TestCase
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
