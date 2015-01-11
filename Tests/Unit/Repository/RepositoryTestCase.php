<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2014 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Component\Resource\Tests\Unit\Repository;

use Prophecy\PhpUnit\ProphecyTestCase;

abstract class RepositoryTestCase extends ProphecyTestCase
{
    public function provideGet()
    {
        return array(
            array(null, '/cmf/foobar', '/cmf/foobar', '/cmf/foobar'),
            array('/site/foo.com', '/cmf/foobar', '/cmf/foobar', '/site/foo.com/cmf/foobar'),
            array('/site/foo.com', '/bar/../foobar', '/foobar', '/site/foo.com/foobar'),
        );
    }

    abstract public function testGet($basePath, $requestedPath, $canonicalPath, $evaluatedPath);

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
     * @expectedException Assert\InvalidArgumentException
     */
    public function testGetInvalid($basePath, $requestedPath)
    {
        $this->getRepository($basePath)->get($requestedPath);
    }

    abstract public function testFind();

    /**
     * @param string $basePath Base path of repository
     * @param string $requestedPath Requested path (may be include relative notation eg. ".,")
     * @param string $canonicalPath The expected resolved path (i.e. ".." has been resolved)
     * @param string $absPath Absolute path to subject in the underlying repository
     */
    abstract public function testListChildren($basePath, $requestedPath, $canonicalPath, $absPath);

    public function provideHasChildren()
    {
        return array(
            array(2, true),
            array(0, false),
        );
    }

    /**
     * @param integer $nbChildren Number of children expected
     * @param boolean $hasChildren Expected result
     */
    abstract public function testHasChildren($nbChildren, $hasChildren);

    abstract protected function getRepository($path = null);
}
