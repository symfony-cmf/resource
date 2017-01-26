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

use Symfony\Cmf\Component\Resource\Puli\ArrayResourceCollection;
use Symfony\Cmf\Component\Resource\Repository\PhpcrRepository;
use Symfony\Cmf\Component\Resource\Repository\Resource\PhpcrResource;

class PhpcrRepositoryTest extends AbstractPhpcrRepositoryTestCase
{
    protected $node;
    protected $node1;
    protected $node2;

    public function setUp()
    {
        parent::setUp();
        $this->node = $this->prophesize('PHPCR\NodeInterface');
        $this->node1 = $this->prophesize('PHPCR\NodeInterface');
        $this->node2 = $this->prophesize('PHPCR\NodeInterface');
    }

    /**
     * {@inheritdoc}
     *
     * @dataProvider provideGet
     */
    public function testGet($basePath, $requestedPath, $canonicalPath, $evaluatedPath)
    {
        $this->session->getNode($evaluatedPath)->willReturn($this->node);
        $this->node->getPath()->willReturn($evaluatedPath);

        $res = $this->getRepository($basePath)->get($requestedPath);

        $this->assertInstanceOf(PhpcrResource::class, $res);

        $this->assertEquals($requestedPath, $res->getPath());
        $this->assertEquals('foobar', $res->getName());
        $this->assertSame($this->node->reveal(), $res->getPayload());
        $this->assertTrue($res->isAttached());
    }

    /**
     * {@inheritdoc}
     */
    public function testFind()
    {
        $this->session->getNode('/cmf/foobar')->willReturn($this->node);
        $this->finder->find('/cmf/*')->willReturn([
            $this->node,
        ]);

        $res = $this->getRepository()->find('/cmf/*');

        $this->assertInstanceOf(ArrayResourceCollection::class, $res);
        $this->assertCount(1, $res);
        $nodeResource = $res->offsetGet(0);
        $this->assertSame($this->node->reveal(), $nodeResource->getPayload());
    }

    /**
     * {@inheritdoc}
     *
     * @dataProvider provideGet
     */
    public function testListChildren($basePath, $requestedPath, $canonicalPath, $absPath)
    {
        $this->session->getNode($absPath)->willReturn($this->node);
        $this->node->getNodes()->willReturn([
            $this->node1, $this->node2,
        ]);
        $this->node1->getPath()->willReturn($absPath.'/node1');
        $this->node2->getPath()->willReturn($absPath.'/node2');

        $res = $this->getRepository($basePath)->listChildren($requestedPath);

        $this->assertInstanceOf(ArrayResourceCollection::class, $res);
        $this->assertCount(2, $res);
        $this->assertInstanceOf(PhpcrResource::class, $res[0]);
        $this->assertEquals($canonicalPath.'/node1', $res[0]->getPath());
    }

    /**
     * {@inheritdoc}
     *
     * @expectedException \RuntimeException
     * @expectedExceptionMessage No PHPCR node could be found at "/test"
     */
    public function testGetNotExisting()
    {
        $this->session->getNode('/test')->willThrow(new \PHPCR\PathNotFoundException());
        $this->getRepository()->get('/test');
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
            $children[] = $this->prophesize('PHPCR\NodeInterface');
        }

        $this->session->getNode('/test')->willReturn($this->node);
        $this->node->getNodes()->willReturn($children);

        $res = $this->getRepository()->hasChildren('/test');

        $this->assertEquals($hasChildren, $res);
    }

    /**
     * {@inheritdoc}
     */
    public function testRemove()
    {
        $this->finder->find('/test/*')->willReturn([
            $this->node1->reveal(),
            $this->node2->reveal(),
        ]);
        $this->node1->getPath()->willReturn('/test/path1');
        $this->node2->getPath()->willReturn('/test/path2');

        $this->node1->remove()->shouldBeCalled();
        $this->node2->remove()->shouldBeCalled();
        $this->session->save()->shouldBeCalled();

        $this->getRepository()->remove('/test/*', 'glob');
    }

    /**
     * {@inheritdoc}
     */
    public function testRemoveException()
    {
        $this->finder->find('/test/path1')->willReturn([
            $this->node1->reveal(),
        ]);
        $this->node1->remove()->willThrow(new \InvalidArgumentException('test'));

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
    public function testMoveException()
    {
        $this->finder->find('/test/path1')->willReturn([
            $this->node1->reveal(),
        ]);
        $this->node1->getPath()->willReturn('/path/to');
        $this->node1->getName()->willReturn('to');
        $this->node1->remove()->willThrow(new \InvalidArgumentException('test'));

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
    public function testMove()
    {
        $this->finder->find('/test/path1')->willReturn([
            $this->node1->reveal(),
        ]);
        $this->node1->getPath()->willReturn('/test/path1');
        $this->session->move('/test/path1', '/foo/bar')->shouldBeCalled();
        $this->session->save()->shouldBeCalled();

        $this->getRepository()->move('/test/path1', '/foo/bar');
    }

    /**
     * {@inheritdoc}
     */
    public function testMoveMultiple()
    {
        $this->finder->find('/test/*')->willReturn([
            $this->node1->reveal(),
            $this->node2->reveal(),
        ]);
        $this->node1->getPath()->willReturn('/test/path1');
        $this->node2->getPath()->willReturn('/test/path2');
        $this->node1->getName()->willReturn('path1');
        $this->node2->getName()->willReturn('path2');

        $this->session->move('/test/path1', '/foo/path1')->shouldBeCalled();
        $this->session->move('/test/path2', '/foo/path2')->shouldBeCalled();
        $this->session->save()->shouldBeCalled();

        $this->getRepository()->move('/test/*', '/foo');
    }

    /**
     * {@inheritdoc}
     */
    public function testReorder()
    {
        $evaluatedPath = '/test/node-1';

        $this->session->getNode($evaluatedPath)->willReturn($this->node->reveal());
        $this->node->getPath()->willReturn($evaluatedPath);
        $this->node->getParent()->willReturn($this->node1->reveal());
        $this->node->getName()->willReturn('node-1');
        $this->node1->getNodeNames()->willReturn(new \ArrayIterator([
            'node-1', 'node-2', 'node-3',
        ]));

        $this->node1->orderBefore('node-1', 'node-3')->shouldBeCalled();
        $this->session->save()->shouldBeCalled();

        $this->getRepository('/test')->reorder('/node-1', 1);
    }

    /**
     * {@inheritdoc}
     */
    public function testReorderToLast()
    {
        $evaluatedPath = '/test/node-1';

        $this->session->getNode($evaluatedPath)->willReturn($this->node->reveal());
        $this->node->getPath()->willReturn($evaluatedPath);
        $this->node->getParent()->willReturn($this->node1->reveal());
        $this->node->getName()->willReturn('node-1');
        $this->node1->getNodeNames()->willReturn([
            'node-1', 'node-2', 'node-3',
        ]);

        $this->node1->orderBefore('node-1', 'node-3')->shouldBeCalled();
        $this->node1->orderBefore('node-3', 'node-1')->shouldBeCalled();
        $this->session->save()->shouldBeCalled();

        $this->getRepository('/test')->reorder('/node-1', 66);
    }

    /**
     * {@inheritdoc}
     */
    protected function getRepository($path = null)
    {
        $repository = new PhpcrRepository($this->session->reveal(), $path, $this->finder->reveal());

        return $repository;
    }
}
