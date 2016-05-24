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
use Symfony\Cmf\Component\Resource\Description\Descriptor;

class DescriptionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Description
     */
    private $description;

    public function setUp()
    {
        $this->description = new Description('some-type');
    }

    /**
     * It should allow values to be set and retrieved.
     */
    public function testGetSet()
    {
        $this->description->set(Descriptor::TYPE_ALIAS, 'page');
        $this->description->set(Descriptor::LINK_EDIT_HTML, '/path/to/edit');
        $this->description->set('custom.key', 'Hello');

        $this->assertEquals('page', $this->description->get(Descriptor::TYPE_ALIAS));
    }

    /**
     * It should throw an exception when requesting an unsupported descriptor.
     *
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Descriptor "not there" not supported for payload type "some-type". Supported descriptors: "foo", "bar"
     */
    public function testGetUnsupported()
    {
        $this->description->set('foo', 'bar');
        $this->description->set('bar', 'foo');
        $this->description->get('not there');
    }
}
