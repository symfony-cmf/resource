<?php

namespace Symfony\Cmf\Component\Resource\Tests\Unit\Repository;

use Symfony\Cmf\Component\Resource\Repository\CompositeRepository;
use Puli\Repository\Api\ResourceRepository;
use Puli\Repository\FilesystemRepository;

class CompositeRepositoryTest extends \PHPUnit_Framework_TestCase
{
    private $repository;

    public function setUp()
    {
        $this->repository = new CompositeRepository();
        $this->repository1 = new FilesystemRepository(__DIR__ . '/data/repo1');
        $this->repository2 = new FilesystemRepository(__DIR__ . '/data/repo2');
    }

    /**
     * It should mount at the root path
     */
    public function testMount()
    {
        $this->repository->mount('/', $this->repository1);
        $resources = $this->repository->listChildren('/');

        $this->assertEquals(array(
            '/dir2', '/file1.txt', '/file2.txt'
        ), $resources->getPaths());
    }

    /**
     * It should mount children under the root path
     * It should list children resources
     * It should say if it has children resources
     */
    public function testMountChildOfRoot()
    {
        $this->repository->mount('/child1', $this->repository1);
        $this->repository->mount('/child2', $this->repository2);

        $this->assertTrue($this->repository->hasChildren('/'));

        $resources = $this->repository->listChildren('/');
        $this->assertEquals(array(
            '/child1', '/child2'
        ), $resources->getPaths());

        $this->assertTrue($this->repository->hasChildren('/child1'));

        $resources = $this->repository->listChildren('/child1');
        $this->assertEquals(array(
            'dir2', 'file1.txt', 'file2.txt'
        ), $resources->getNames());
    }

    /**
     * It should mount one repository within another
     */
    public function testMountWithinAnother()
    {
        $this->repository->mount('/', $this->repository1);
        $this->repository->mount('/child', $this->repository2);

        $resources = $this->repository->listChildren('/');
        $this->assertEquals(array(
            'child', 'dir2', 'file1.txt', 'file2.txt'
        ), $resources->getNames());

        $resources = $this->repository->listChildren('/child');
        $this->assertEquals(array(
            'file1.txt',
        ), $resources->getNames());
    }

    /**
     * It should get a resource at a specific path in a nested repository
     */
    public function testGetResource()
    {
        $this->repository->mount('/', $this->repository1);
        $this->repository->mount('/child', $this->repository2);

        $resource = $this->repository->get('/child/file1.txt');
        $this->assertNotNull($resource);
        $this->assertEquals('/child/file1.txt', $resource->getPath());
        $repository = $resource->getRepository();
        $this->assertInstanceOf('Puli\Repository\FilesystemRepository', $repository);
    }

    /**
     * It should find resources
     * It should say if it contains resources
     *
     * @expectedException BadMethodCallException 
     */
    public function testFindResources()
    {
        $this->repository->mount('/', $this->repository1);
        $this->repository->mount('/child', $this->repository2);

        $resources = $this->repository->find('/*');

        $this->assertTrue($this->repository->contains('/*'));
        // TODO: Why does this return "/" ?
        $this->assertEquals(array(
            '', 'child', 'dir2', 'file1.txt', 'file2.txt'
        ), $resources->getNames());

        $this->assertTrue($this->repository->contains('/*/*'));
        $resources = $this->repository->find('/*/*');

        $this->assertEquals(array(
            '/child/file1.txt',
            '/dir2/file1.txt'
        ), $resources->getPaths());

        $this->assertFalse($this->repository->contains('/idoesnotexist'));
    }
}
