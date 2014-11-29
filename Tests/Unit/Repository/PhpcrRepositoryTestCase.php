<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2014 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Component\DependencyInjection;

use Prophecy\PhpUnit\ProphecyTestCase;

class PhpcrOdmRepositoryTest extends ProphecyTestCase
{
    public function setUp()
    {
        $this->finder = $this->prophesize('Symfony\Cmf\Component\Resource\FinderInterface');
    }

    public function provideGet()
    {
        return array(
            array('/cmf/foo', '/cmf/foobar', '/cmf/foo/cmf/foobar'),
            array(null, '/cmf/foobar', '/cmf/foobar'),
        );
    }
}

