<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2017 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Component\Resource\Tests\Unit\Repository;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ODM\PHPCR\ChildrenCollection;
use Doctrine\ODM\PHPCR\DocumentManagerInterface;
use Doctrine\ODM\PHPCR\UnitOfWork;
use PHPCR\NodeInterface;
use Prophecy\Argument;
use Symfony\Cmf\Component\Resource\Puli\ArrayResourceCollection;
use Symfony\Cmf\Component\Resource\Repository\PhpcrOdmRepository;
use Symfony\Cmf\Component\Resource\Repository\Resource\PhpcrOdmResource;

class PhpcrOdmRepositoryTest extends AbstractPhpcrRepositoryTestCase
{
    private $documentManager;
    private $managerRegistry;
    private $childrenCollection;
    private $uow;
    private $document;
    private $child1;
    private $child2;
    private $node1;
    private $node2;

    public function setUp()
    {
        parent::setUp();
        $this->documentManager = $this->prophesize(DocumentManagerInterface::class);
        $this->managerRegistry = $this->prophesize(ManagerRegistry::class);
        $this->childrenCollection = $this->prophesize(ChildrenCollection::class);
        $this->uow = $this->prophesize(UnitOfWork::class);
        $this->document = new \stdClass();

        $this->child1 = new \stdClass();

        // because Prophecy doesn't care much about object IDs...
        $this->child2 = new stdClass2();

        $this->node1 = $this->prophesize(NodeInterface::class);
        $this->node2 = $this->prophesize(NodeInterface::class);

        $this->managerRegistry->getManager()->willReturn($this->documentManager);
        $this->documentManager->getUnitOfWork()->willReturn($this->uow->reveal());
    }

    /**
     * {@inheritdoc}
     *
     * @dataProvider provideGet
     */
    public function testGet($basePath, $requestedPath, $canonicalPath, $evaluatedPath)
    {
        $this->documentManager->find(null, $evaluatedPath)->willReturn($this->document);

        $res = $this->getRepository($basePath)->get($requestedPath);

        $this->assertInstanceOf('Symfony\Cmf\Component\Resource\Repository\Resource\PhpcrOdmResource', $res);
        $this->assertSame($this->document, $res->getPayload());
        $this->assertTrue($res->isAttached());
    }

    /**
     * {@inheritdoc}
     */
    public function testFind()
    {
        $this->documentManager->find(null, '/base/path/cmf/foobar')->willReturn($this->document);
        $this->uow->getDocumentId($this->document)->willReturn('/cmf/foobar');

        $this->finder->find('/base/path/cmf/*')->willReturn([
            $this->document,
        ]);

        $res = $this->getRepository('/base/path')->find('/cmf/*');

        $this->assertInstanceOf(ArrayResourceCollection::class, $res);
        $this->assertCount(1, $res);
        $documentResource = $res->offsetGet(0);
        $this->assertSame($this->document, $documentResource->getPayload());
    }

    /**
     * {@inheritdoc}
     *
     * @dataProvider provideGet
     */
    public function testListChildren($basePath, $requestedPath, $canonicalPath, $absPath)
    {
        $this->documentManager->find(null, $absPath)->willReturn($this->document);
        $this->childrenCollection->toArray()->willReturn([
            $this->child1, $this->child2,
        ]);
        $this->documentManager->getChildren($this->document)->willReturn($this->childrenCollection);
        $this->uow->getDocumentId($this->child1)->willReturn($absPath.'/child1');
        $this->uow->getDocumentId($this->child2)->willReturn($absPath.'/child2');

        $res = $this->getRepository($basePath)->listChildren($requestedPath);

        $this->assertInstanceOf(ArrayResourceCollection::class, $res);
        $this->assertCount(2, $res);
        $this->assertInstanceOf(PhpcrOdmResource::class, $res[0]);
        $this->assertEquals($canonicalPath.'/child1', $res[0]->getPath());
    }

    /**
     * {@inheritdoc}
     *
     * @dataProvider provideHasChildren
     */
    public function testHasChildren($nbChildren, $hasChildren)
    {
        $children = [];
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
     * {@inheritdoc}
     *
     * @expectedException \RuntimeException
     * @expectedExceptionMessage No PHPCR-ODM document could be found at "/test"
     */
    public function testGetNotExisting()
    {
        $this->documentManager->find(null, '/test')->willReturn(null);
        $this->getRepository()->get('/test');
    }

    /**
     * {@inheritdoc}
     */
    public function testRemove()
    {
        $this->finder->find('/test/*')->willReturn([
            $this->child1,
            $this->child2,
        ]);

        $this->documentManager->remove($this->child1)->shouldBeCalled();
        $this->documentManager->remove($this->child2)->shouldBeCalled();
        $this->documentManager->flush()->shouldBeCalled();

        $number = $this->getRepository()->remove('/test/*', 'glob');
        $this->assertEquals(2, $number);
    }

    /**
     * {@inheritdoc}
     */
    public function testRemoveException()
    {
        $this->finder->find('/test/path1')->willReturn([
            $this->document,
        ]);
        $this->documentManager->remove($this->document)->willThrow(new \InvalidArgumentException('test'));

        try {
            $this->getRepository()->remove('/test/path1');
        } catch (\Exception $e) {
            $this->assertWrappedException(
                \RuntimeException::class,
                'Error encountered when removing resource(s) using query "/test/path1"',
                \InvalidArgumentException::class,
                'test',
                $e
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function testMove()
    {
        $this->finder->find('/test/path1')->willReturn([
            $this->document,
        ]);
        $this->documentManager->move($this->document, '/foo/bar')->shouldBeCalled();
        $this->documentManager->flush()->shouldBeCalled();

        $number = $this->getRepository()->move('/test/path1', '/foo/bar');

        $this->assertEquals(1, $number);
    }

    /**
     * {@inheritdoc}
     */
    public function testMoveMultiple()
    {
        $this->finder->find('/test/*')->willReturn([
            $this->child1,
            $this->child2,
        ]);

        $this->documentManager->getNodeForDocument(Argument::exact($this->child1))->willReturn($this->node1->reveal());
        $this->documentManager->getNodeForDocument(Argument::exact($this->child2))->willReturn($this->node2->reveal());
        $this->node1->getName()->willReturn('path1');
        $this->node2->getName()->willReturn('path2');

        $this->documentManager->move(Argument::exact($this->child1), '/foo/path1')->shouldBeCalled();
        $this->documentManager->move(Argument::exact($this->child2), '/foo/path2')->shouldBeCalled();
        $this->documentManager->flush()->shouldBeCalled();

        $number = $this->getRepository()->move('/test/*', '/foo');

        $this->assertEquals(2, $number);
    }

    /**
     * {@inheritdoc}
     */
    public function testMoveException()
    {
        $this->finder->find('/test/path1')->willReturn([
            $this->document,
        ]);
        $this->documentManager->move($this->document, '/test/path2')->willThrow(new \InvalidArgumentException('test'));

        try {
            $this->getRepository()->move('/test/path1', '/test/path2');
        } catch (\Exception $e) {
            $this->assertWrappedException(
                \RuntimeException::class,
                'Error encountered when moving resource(s) using query "/test/path1"',
                \InvalidArgumentException::class,
                'test',
                $e
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function testReorder()
    {
        $this->doTestReorder(1, true);
    }

    /**
     * {@inheritdoc}
     */
    public function testReorderToLast()
    {
        $this->doTestReorder(66, false);
    }

    private function doTestReorder($position, $before)
    {
        $evaluatedPath = '/test/foo';

        $this->documentManager->find(null, $evaluatedPath)->willReturn($this->child1);
        $this->documentManager->getNodeForDocument($this->child1)->willReturn($this->node1->reveal());
        $this->node1->getParent()->willReturn($this->node2->reveal());
        $this->node1->getName()->willReturn('foo');
        $this->node2->getNodeNames()->willReturn(new \ArrayIterator([
            'foo', 'bar', 'baz',
        ]));
        $this->node2->getPath()->willReturn('/test');
        $this->documentManager->find(null, '/test')->willReturn($this->document);
        $this->documentManager->reorder($this->document, 'foo', 'baz', $before)->shouldBeCalled();
        $this->documentManager->flush()->shouldBeCalled();

        $this->getRepository('/test')->reorder('/foo', $position);
    }

    protected function getRepository($path = null)
    {
        $repository = new PhpcrOdmRepository($this->managerRegistry->reveal(), $path, $this->finder->reveal());

        return $repository;
    }
}

class stdClass2 extends \stdClass
{
}
