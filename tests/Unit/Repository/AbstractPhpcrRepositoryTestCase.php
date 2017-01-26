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

abstract class AbstractPhpcrRepositoryTestCase extends \PHPUnit_Framework_TestCase
{
    protected $finder;
    protected $session;

    public function setUp()
    {
        $this->session = $this->prophesize('PHPCR\SessionInterface');
        $this->finder = $this->prophesize('DTL\Glob\FinderInterface');
    }

    abstract public function testGetNotExisting();

    /**
     * @param int  $nbChildren  Number of children expected
     * @param bool $hasChildren Expected result
     */
    abstract public function testHasChildren($nbChildren, $hasChildren);

    public function provideHasChildren()
    {
        return [
            [2, true],
            [0, false],
        ];
    }

    /**
     * @param string $path
     */
    abstract protected function getRepository($path = null);

    /**
     * @param string $basePath      Base path of repository
     * @param string $requestedPath Requested path (may be include relative notation eg. ".,")
     * @param string $canonicalPath The expected resolved path (i.e. ".." has been resolved)
     * @param string $absPath       Absolute path to subject in the underlying repository
     */
    abstract public function testListChildren($basePath, $requestedPath, $canonicalPath, $absPath);

    abstract public function testGet($basePath, $requestedPath, $canonicalPath, $evaluatedPath);

    public function provideGet()
    {
        return [
            [null, '/cmf/foobar', '/cmf/foobar', '/cmf/foobar'],
            ['/site/foo.com', '/cmf/foobar', '/cmf/foobar', '/site/foo.com/cmf/foobar'],
            ['/site/foo.com', '/bar/../foobar', '/foobar', '/site/foo.com/foobar'],
        ];
    }

    abstract public function testFind();

    abstract public function testRemove();

    abstract public function testMove();

    abstract public function testMoveMultiple();

    abstract public function testMoveException();

    abstract public function testRemoveException();

    abstract public function testReorder();

    abstract public function testReorderToLast();

    /**
     * @dataProvider provideGetInvalid
     * @expectedException \InvalidArgumentException
     */
    public function testGetInvalid($basePath, $requestedPath)
    {
        $this->getRepository($basePath)->get($requestedPath);
    }

    public function provideGetInvalid()
    {
        return [
            [null, 'cmf/foobar'],
            [null, ''],
            [null, new \stdClass()],
            ['asd', 'asd'],
        ];
    }

    /**
     * Clear is not supported.
     *
     * @expectedException \Exception
     */
    public function testClearShouldThrow()
    {
        $this->getRepository()->clear();
    }

    /**
     * When removing, it should return 0 as number of items if no nodes are found.
     */
    public function testRemoveZeroNodesFound()
    {
        $this->finder->find('/path/to/foo')->willReturn([]);
        $numberOfNodes = $this->getRepository()->remove('/path/to/foo');
        $this->assertEquals(0, $numberOfNodes);
    }

    /**
     * When moving, it should return 0 as number of items if no nodes are found.
     */
    public function testMoveZeroNodes()
    {
        $this->finder->find('/path/to/foo')->willReturn([]);
        $numberOfNodes = $this->getRepository()->move('/path/to/foo', '/bar/bar');
        $this->assertEquals(0, $numberOfNodes);
    }

    protected function assertWrappedException($outerClass, $outerMessage, $innerClass, $innerMessage, \Exception $e)
    {
        $this->assertInstanceOf($outerClass, $e);
        $this->assertContains($outerMessage, $e->getMessage());

        $previous = $e->getPrevious();

        $this->assertNotNull($previous);
        $this->assertInstanceOf($innerClass, $previous);
        $this->assertContains($innerMessage, $previous->getMessage());
    }
}
