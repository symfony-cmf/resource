<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2017 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Component\Resource\Tests\Unit\Description\Enhancer\Doctrine;

use Doctrine\ODM\PHPCR\Mapping\ClassMetadata;
use Doctrine\ODM\PHPCR\Mapping\ClassMetadataFactory;
use Symfony\Cmf\Component\Resource\Description\Description;
use Symfony\Cmf\Component\Resource\Description\Descriptor;
use Symfony\Cmf\Component\Resource\Description\Enhancer\Doctrine\PhpcrOdmEnhancer;
use Symfony\Cmf\Component\Resource\Puli\Api\PuliResource;
use Symfony\Cmf\Component\Resource\Repository\Resource\CmfResource;

class PhpcrOdmEnhancerTest extends \PHPUnit_Framework_TestCAse
{
    private $metadataFactory;
    private $enhancer;
    private $cmfResource;
    private $puliResource;
    private $odmMetadata;
    private $description;

    public function setUp()
    {
        $this->metadataFactory = $this->prophesize(ClassMetadataFactory::class);
        $this->enhancer = new PhpcrOdmEnhancer($this->metadataFactory->reveal());

        $this->cmfResource = $this->prophesize(CmfResource::class);
        $this->puliResource = $this->prophesize(PuliResource::class);
        $this->odmMetadata = $this->prophesize(ClassMetadata::class);
        $this->description = $this->prophesize(Description::class);
    }

    /**
     * It should return true it supports a given resource.
     */
    public function testSupportsResource()
    {
        $this->cmfResource->getPayloadType()->willReturn(\stdClass::class);
        $this->metadataFactory->hasMetadataFor(\stdClass::class)->willReturn(true);

        $result = $this->enhancer->supports($this->cmfResource->reveal());
        $this->assertTrue($result);
    }

    /**
     * It should return false if the resource is not an instance of CmfResource.
     */
    public function testNotSupportsNonCmfResource()
    {
        $this->assertFalse(
            $this->enhancer->supports($this->puliResource->reveal())
        );
    }

    /**
     * It should return false if the resource is not known by the PHPCR-ODM metadata factory.
     */
    public function testNotSupportsNotSupportedByPhpcrOdm()
    {
        $this->cmfResource->getPayloadType()->willReturn(\stdClass::class);
        $this->metadataFactory->hasMetadataFor(\stdClass::class)->willReturn(false);

        $this->assertFalse(
            $this->enhancer->supports($this->cmfResource->reveal())
        );
    }

    /**
     * It should enhance the description with the child mapping information from the PHPCR-ODM metadata.
     */
    public function testEnhanceDescription()
    {
        // object the implements an allowed interface
        $mappedObject1 = $this->prophesize();
        $mappedObject1->willImplement(FooInterface::class);
        $metadata1 = $this->prophesize(ClassMetadata::class);
        $metadata1->name = get_class($mappedObject1->reveal());
        $metadata1->getReflectionClass()->willReturn(new \ReflectionClass($metadata1->name));

        // object the extends an allowed abstract class
        $mappedObject2 = $this->prophesize();
        $mappedObject2->willExtend(AbstractFoo::class);
        $metadata2 = $this->prophesize(ClassMetadata::class);
        $metadata2->name = get_class($mappedObject2->reveal());
        $metadata2->getReflectionClass()->willReturn(new \ReflectionClass($metadata2->name));

        // object of exact type that is allowed
        $mappedObject3 = $this->prophesize();
        $metadata3 = $this->prophesize(ClassMetadata::class);
        $metadata3->name = get_class($mappedObject3->reveal());
        $metadata3->getReflectionClass()->willReturn(new \ReflectionClass($metadata3->reveal()));

        // object that is not permitted
        $metadata4 = $this->prophesize(ClassMetadata::class);
        $metadata4->name = NotAllowedFoo::class;
        $metadata4->getReflectionClass()->willReturn(new \ReflectionClass($metadata4->reveal()));

        $this->description->getResource()->willReturn($this->cmfResource->reveal());
        $this->cmfResource->getPayloadType()->willReturn('payload_type');
        $this->metadataFactory->getMetadataFor('payload_type')->willReturn($this->odmMetadata->reveal());
        $this->metadataFactory->getAllMetadata()->willReturn([
            $metadata1->reveal(),
            $metadata2->reveal(),
            $metadata3->reveal(),
        ]);

        $this->odmMetadata->isLeaf()->willReturn(false);
        $this->odmMetadata->getChildClasses()->willReturn([
            FooInterface::class,
            AbstractFoo::class,
            $metadata3->name,
        ]);

        $this->description->set(Descriptor::CHILDREN_ALLOW, true)->shouldBeCalled();
        $this->description->set(Descriptor::CHILDREN_TYPES, [
            $metadata1->name,
            $metadata2->name,
            $metadata3->name,
        ])->shouldBeCalled();
        $this->enhancer->enhance($this->description->reveal());
    }
}

interface FooInterface
{
}

abstract class AbstractFoo
{
}

class NotAllowedFoo
{
}
