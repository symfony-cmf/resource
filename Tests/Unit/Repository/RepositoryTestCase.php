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
use Symfony\Cmf\Component\Resource\Repository\Resource\CmfResource;

abstract class RepositoryTestCase extends \PHPUnit_Framework_TestCase
{
    protected $documentManager;
    protected $managerRegistry;
    protected $childrenCollection;
    protected $finder;
    protected $uow;
    protected $document;
    protected $child1;
    protected $child2;
    protected $object;
    protected $resource;
    protected $session;
    protected $node;
    protected $rootNode;

    public function setUp()
    {
        $this->session = $this->prophesize('PHPCR\SessionInterface');
        $this->finder = $this->prophesize('DTL\Glob\FinderInterface');
        $this->node = $this->prophesize('PHPCR\NodeInterface');
        $this->rootNode = $this->prophesize('PHPCR\NodeInterface');
        $this->session->getRootNode()->willReturn($this->rootNode);
    }

    public function provideGet()
    {
        return array(
            array(null, '/cmf/foobar', '/cmf/foobar', '/cmf/foobar'),
            array('/site/foo.com', '/cmf/foobar', '/cmf/foobar', '/site/foo.com/cmf/foobar'),
            array('/site/foo.com', '/bar/../foobar', '/foobar', '/site/foo.com/foobar'),
        );
    }

    public function provideGetInvalid()
    {
        return array(
            array(null, 'cmf/foobar'),
            array(null, ''),
            array(null, new \stdClass()),
            array('asd', 'asd'),
        );
    }

    /**
     * @dataProvider provideGetInvalid
     * @expectedException \InvalidArgumentException
     */
    public function testGetInvalid($basePath, $requestedPath)
    {
        $this->getRepository($basePath)->get($requestedPath);
    }

    public function provideHasChildren()
    {
        return array(
            array(2, true),
            array(0, false),
        );
    }

    public function provideAddInvalid()
    {
        return [
            ['/', null],
            ['', null],
            ['/test', null, true],
            ['/test', new CmfResource()],
            ['/test', new ArrayResourceCollection([new CmfResource()])],
        ];
    }

    abstract public function testGetNotExisting();

    /**
     * @param int  $nbChildren  Number of children expected
     * @param bool $hasChildren Expected result
     */
    abstract public function testHasChildren($nbChildren, $hasChildren);

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

    abstract public function testFind();

    abstract public function testGet($basePath, $requestedPath, $canonicalPath, $evaluatedPath);

    abstract public function testGetVersion();
}
