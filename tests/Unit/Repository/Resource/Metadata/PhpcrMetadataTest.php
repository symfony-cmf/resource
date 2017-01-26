<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2017 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Component\Resource\Unit\Repository\Metadata\Resource;

use Symfony\Cmf\Component\Resource\Repository\Resource\Metadata\PhpcrMetadata;

class PhpcrMetadataTest extends \PHPUnit_Framework_TestCase
{
    private $node;
    private $property;

    public function setUp()
    {
        $this->node = $this->prophesize('PHPCR\NodeInterface');
        $this->property = $this->prophesize('PHPCR\PropertyInterface');
        $this->metadata = new PhpcrMetadata($this->node->reveal());
    }

    /**
     * @dataProvider provideMethods
     */
    public function testMethods($method, $mixinType, $hasMixin, $propertyName, $propertyValue, $expectedValue)
    {
        $this->node->isNodeType($mixinType)->willReturn($hasMixin);
        $this->node->getProperty($propertyName)->willReturn($this->property->reveal());
        $this->property->getDate()->willReturn($propertyValue);

        $res = $this->metadata->{$method}();
        $this->assertEquals($expectedValue, $res);
    }

    public function provideMethods()
    {
        return [
            [
                'getCreationTime',
                'mix:created',
                true,
                'jcr:created',
                new \DateTime('2015-01-01T00:00:00Z'),
                1420070400,
            ],
            [
                'getModificationTime',
                'mix:lastModified',
                true,
                'jcr:lastModified',
                new \DateTime('2015-01-01T00:00:00Z'),
                1420070400,
            ],
            [
                'getCreationTime',
                'mix:created',
                false,
                null,
                null,
                0,
            ],
            [
                'getModificationTime',
                'mix:lastModified',
                false,
                null,
                null,
                0,
            ],
            [
                'getAccessTime',
                null,
                null,
                null,
                null,
                0,
            ],
        ];
    }
}
