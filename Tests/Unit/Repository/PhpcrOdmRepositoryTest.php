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

use Puli\Repository\Resource\Collection\ArrayResourceCollection;
use Symfony\Cmf\Component\Resource\Repository\PhpcrOdmRepository;
use Symfony\Cmf\Component\Resource\Repository\Resource\PhpcrOdmResource;

class PhpcrOdmRepositoryTest extends RepositoryTestCase
{
    protected $documentManager;
    protected $managerRegistry;
    protected $childrenCollection;
    protected $uow;
    protected $document;
    protected $object;
    protected $child1;
    protected $child2;

    public function setUp()
    {
        parent::setUp();
        $this->documentManager = $this->prophesize('Doctrine\ODM\PHPCR\DocumentManager');
        $this->managerRegistry = $this->prophesize('Doctrine\Common\Persistence\ManagerRegistry');
        $this->childrenCollection = $this->prophesize('Doctrine\ODM\PHPCR\ChildrenCollection');
        $this->uow = $this->prophesize('Doctrine\ODM\PHPCR\UnitOfWork');
        $this->document = $this->prophesize('PHPCR\DocumentInterface');
        $this->child1 = new \stdClass();
        $this->child2 = new \stdClass();
        $this->object = new \stdClass();

        $this->managerRegistry->getManager()->willReturn($this->documentManager);
        $this->documentManager->getUnitOfWork()->willReturn($this->uow->reveal());
    }

    /**
     * @dataProvider provideGet
     */
    public function testGet($basePath, $requestedPath, $canonicalPath, $evaluatedPath)
    {
        $this->documentManager->find(null, $evaluatedPath)->willReturn($this->object);

        $res = $this->getRepository($basePath)->get($requestedPath);

        $this->assertInstanceOf('Symfony\Cmf\Component\Resource\Repository\Resource\PhpcrOdmResource', $res);
        $this->assertSame($this->object, $res->getPayload());
        $this->assertTrue($res->isAttached());
    }

    public function testFind()
    {
        $this->documentManager->find(null, '/base/path/cmf/foobar')->willReturn($this->document);
        $this->uow->getDocumentId($this->document)->willReturn('/cmf/foobar');

        $this->finder->find('/base/path/cmf/*')->willReturn(array(
            $this->document,
        ));

        $res = $this->getRepository('/base/path')->find('/cmf/*');

        $this->assertInstanceOf('Puli\Repository\Resource\Collection\ArrayResourceCollection', $res);
        $this->assertCount(1, $res);
        $documentResource = $res->offsetGet(0);
        $this->assertSame($this->document->reveal(), $documentResource->getPayload());
    }

    /**
     * @dataProvider provideGet
     */
    public function testListChildren($basePath, $requestedPath, $canonicalPath, $absPath)
    {
        $this->documentManager->find(null, $absPath)->willReturn($this->document);
        $this->childrenCollection->toArray()->willReturn(array(
            $this->child1, $this->child2,
        ));
        $this->documentManager->getChildren($this->document)->willReturn($this->childrenCollection);
        $this->uow->getDocumentId($this->child1)->willReturn($absPath.'/child1');
        $this->uow->getDocumentId($this->child2)->willReturn($absPath.'/child2');

        $res = $this->getRepository($basePath)->listChildren($requestedPath);

        $this->assertInstanceOf('Puli\Repository\Resource\Collection\ArrayResourceCollection', $res);
        $this->assertCount(2, $res);
        $this->assertInstanceOf('Symfony\Cmf\Component\Resource\Repository\Resource\PhpcrOdmResource', $res[0]);
        $this->assertEquals($canonicalPath.'/child2', $res[0]->getPath());
    }

    /**
     * @dataProvider provideHasChildren
     */
    public function testHasChildren($nbChildren, $hasChildren)
    {
        $children = array();
        for ($i = 0; $i < $nbChildren; ++$i) {
            $children[] = new \stdClass();
        }

        $this->childrenCollection->toArray()->willReturn($children);
        $this->documentManager->find(null, '/test')->willReturn($this->document);
        $this->documentManager->getChildren($this->document)->willReturn($this->childrenCollection);

        $res = $this->getRepository()->hasChildren('/test');

        $this->assertEquals($hasChildren, $res);
    }

    /**
     * @expectedException Puli\Repository\Api\ResourceNotFoundException
     */
    public function testGetNotExisting()
    {
        $this->documentManager->find(null, '/test')->willReturn(null);
        $this->getRepository()->get('/test');
    }

    protected function getRepository($path = null)
    {
        $repository = new PhpcrOdmRepository($this->managerRegistry->reveal(), $path, $this->finder->reveal());

        return $repository;
    }

    public function testGetVersion()
    {
        $this->documentManager->find(null, '/test')->willReturn($this->object);

        $this->assertInstanceOf(
            '\Puli\Repository\Api\ChangeStream\VersionList',
            $this->getRepository()->getVersions('/test')
        );
    }

    /**
     * @expectedException \Puli\Repository\Api\NoVersionFoundException
     */
    public function testGetVersionsWillThrow()
    {
        $this->documentManager->find(null, '/test')->willReturn(null);

        $this->getRepository()->getVersions('/test');
    }

    /**
     * @dataProvider provideAddInvalid
     */
    public function testAddWillThrowForNonValidParameters($path, $resource, $expectedExceptionMessage, $noParent = false)
    {
        $this->documentManager->find(null, '/test')->willReturn($noParent ? null : $this->document);
        $this->setExpectedException(\InvalidArgumentException::class, $expectedExceptionMessage);

        $this->getRepository()->add($path, $resource);
    }

    public function testAddWillPersistResource()
    {
        $resource = new PhpcrOdmResource('/test', $this->document);

        $this->documentManager->find(null, '/test')->willReturn($this->object);

        $this->documentManager->persist($this->document)->shouldBeCalled();
        $this->documentManager->flush()->shouldBeCalled();

        $this->getRepository()->add('/test', $resource);
    }

    public function testAddWillPersistResourceCollection()
    {
        $resource = new PhpcrOdmResource('/test', $this->document);

        $this->documentManager->find(null, '/test')->willReturn($this->object);

        $this->documentManager->persist($this->document)->shouldBeCalled();
        $this->documentManager->flush()->shouldBeCalled();

        $this->getRepository()->add('/test', new ArrayResourceCollection([$resource]));
    }

    public function testRemove()
    {
        $this->documentManager->find(null, '/test')->willReturn($this->document);

        $this->childrenCollection->toArray()->willReturn(array(
            $this->child1, $this->child2,
        ));
        $this->documentManager->getChildren($this->document)->willReturn($this->childrenCollection);

        $this->documentManager->remove($this->document)->shouldBeCalled();
        $this->documentManager->flush()->shouldBeCalled();

        $this->getRepository()->remove('/test', 'glob');
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage No document found at /source
     */
    public function testFailingMoveOnSourceNotFound()
    {
        $this->documentManager->find(null, '/source')->willReturn(null);
        $this->getRepository()->move('/source', '/target');
    }

    public function testSuccessfulMove()
    {
        $this->documentManager->find(null, '/source')->willReturn($this->document);

        $this->documentManager->move($this->document, '/target')->shouldBeCalled();
        $this->documentManager->flush()->shouldBeCalled();

        $this->getRepository()->move('/source', '/target');
    }
}
