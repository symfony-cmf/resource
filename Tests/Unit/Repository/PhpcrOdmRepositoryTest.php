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

use Puli\Repository\Api\ChangeStream\VersionList;
use Symfony\Cmf\Component\Resource\Repository\PhpcrOdmRepository;
use Symfony\Cmf\Component\Resource\Repository\Resource\CmfResource;

class PhpcrOdmRepositoryTest extends RepositoryTestCase
{
    public function setUp()
    {
        $this->documentManager = $this->prophesize('Doctrine\ODM\PHPCR\DocumentManager');
        $this->managerRegistry = $this->prophesize('Doctrine\Common\Persistence\ManagerRegistry');
        $this->childrenCollection = $this->prophesize('Doctrine\ODM\PHPCR\ChildrenCollection');
        $this->finder = $this->prophesize('DTL\Glob\FinderInterface');
        $this->uow = $this->prophesize('Doctrine\ODM\PHPCR\UnitOfWork');
        $this->document = new \stdClass();
        $this->child1 = new \stdClass();
        $this->child2 = new \stdClass();

        $this->managerRegistry->getManager()->willReturn($this->documentManager);
        $this->documentManager->getUnitOfWork()->willReturn($this->uow->reveal());

        $this->object = new \stdClass();
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
        $this->assertSame($this->document, $documentResource->getPayload());
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
        $this->assertEquals(new VersionList('some-path', [new CmfResource('some-path')]), $this->getRepository()->getVersions('some-path'));
    }
}
