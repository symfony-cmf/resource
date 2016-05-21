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

use PHPCR\NodeType\NodeTypeInterface;
use Symfony\Cmf\Component\Resource\Repository\PhpcrRepository;
use Symfony\Cmf\Component\Resource\Repository\Resource\PhpcrResource;

class PhpcrRepositoryTest extends RepositoryTestCase
{
    public function setUp()
    {
        parent::setUp();
        $this->child1 = $this->prophesize('PHPCR\NodeInterface');
        $this->child2 = $this->prophesize('PHPCR\NodeInterface');
    }

    /**
     * @dataProvider provideGet
     */
    public function testGet($basePath, $requestedPath, $canonicalPath, $evaluatedPath)
    {
        $this->session->getNode($evaluatedPath)->willReturn($this->node);
        $this->node->getPath()->willReturn($evaluatedPath);

        $res = $this->getRepository($basePath)->get($requestedPath);

        $this->assertInstanceOf('Symfony\Cmf\Component\Resource\Repository\Resource\PhpcrResource', $res);

        $this->assertEquals($requestedPath, $res->getPath());
        $this->assertEquals('foobar', $res->getName());
        $this->assertSame($this->node->reveal(), $res->getPayload());
        $this->assertTrue($res->isAttached());
    }

    public function testFind()
    {
        $this->session->getNode('/cmf/foobar')->willReturn($this->node);
        $this->finder->find('/cmf/*')->willReturn(array(
            $this->node,
        ));

        $res = $this->getRepository()->find('/cmf/*');

        $this->assertInstanceOf('Puli\Repository\Resource\Collection\ArrayResourceCollection', $res);
        $this->assertCount(1, $res);
        $nodeResource = $res->offsetGet(0);
        $this->assertSame($this->node->reveal(), $nodeResource->getPayload());
    }

    /**
     * @dataProvider provideGet
     */
    public function testListChildren($basePath, $requestedPath, $canonicalPath, $absPath)
    {
        $this->session->getNode($absPath)->willReturn($this->node);
        $this->node->getNodes()->willReturn(array(
            $this->child1, $this->child2,
        ));
        $this->child1->getPath()->willReturn($absPath.'/child1');
        $this->child2->getPath()->willReturn($absPath.'/child2');

        $res = $this->getRepository($basePath)->listChildren($requestedPath);

        $this->assertInstanceOf('Puli\Repository\Resource\Collection\ArrayResourceCollection', $res);
        $this->assertCount(2, $res);
        $this->assertInstanceOf('Symfony\Cmf\Component\Resource\Repository\Resource\PhpcrResource', $res[0]);
        $this->assertEquals($canonicalPath.'/child1', $res[0]->getPath());
    }

    /**
     * @expectedException \Puli\Repository\Api\ResourceNotFoundException
     */
    public function testGetNotExisting()
    {
        $this->session->getNode('/test')->willThrow(new \PHPCR\PathNotFoundException());
        $this->getRepository()->get('/test');
    }

    /**
     * @dataProvider provideHasChildren
     */
    public function testHasChildren($nbChildren, $hasChildren)
    {
        $children = array();
        for ($i = 0; $i < $nbChildren; ++$i) {
            $children[] = $this->prophesize('PHPCR\NodeInterface');
        }

        $this->session->getNode('/test')->willReturn($this->node);
        $this->node->getNodes()->willReturn($children);

        $res = $this->getRepository()->hasChildren('/test');

        $this->assertEquals($hasChildren, $res);
    }

    protected function getRepository($path = null)
    {
        $repository = new PhpcrRepository($this->session->reveal(), $path, $this->finder->reveal());

        return $repository;
    }

    public function testGetVersion()
    {
        $this->session->getNode('/test')->willReturn($this->node);
        $this->node->getPath()->willReturn('/test');

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
        $this->session->getNode('/test')->willThrow('\PHPCR\PathNotFoundException');

        $this->getRepository()->getVersions('/test');
    }

    /**
     * @dataProvider provideAddInvalid
     *
     * @expectedException \InvalidArgumentException
     */
    public function testAddWillThrowForNonValidParameters($path, $resource, $noParentNode = false)
    {
        $this->session->getNode('/')->willReturn($noParentNode ? null : $this->node);

        if ($noParentNode) {
            $this->rootNode->hasNode('test')->willReturn(false);
            $this->rootNode->addNode('test')->willReturn(null);
        } else {
            $this->rootNode->hasNode('test')->willReturn(true);
            $this->rootNode->getNode('test')->willReturn($this->node);
        }

        $this->session->save()->shouldNotBeCalled();

        $this->getRepository()->add($path, $resource);
    }

    public function testAddWillPersist()
    {
        $resource = new PhpcrResource('/test', $this->node->reveal());

        $nodeType = $this->prophesize(NodeTypeInterface::class);
        $this->node->getPrimaryNodeType()->willReturn($nodeType);
        $nodeType->getName()->willReturn('class-name');
        $this->session->getNode('/')->willReturn($this->node);

        $this->session->save()->shouldBeCalled();
        $this->node->addNode('test', 'class-name')->shouldBeCalled();

        $this->getRepository()->add('/test', $resource);
    }

    public function testRemove()
    {
        $this->session->getNodes('/test')->willReturn([1, 2, 3]);
        $this->session->removeItem('/test')->shouldBeCalled();
        $this->session->save()->shouldBeCalled();

        $deleted = $this->getRepository()->remove('/test', 'glob');
        $this->assertEquals(3, $deleted);
    }
}
