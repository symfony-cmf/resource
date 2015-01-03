<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2014 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Component\Resource\Repository;

use Prophecy\PhpUnit\ProphecyTestCase;
use Symfony\Cmf\Component\Resource\Repository\PhpcrOdmRepository;

class PhpcrOdmRepositoryTest extends ProphecyTestCase
{
    public function setUp()
    {
        $this->documentManager = $this->prophesize('Doctrine\ODM\PHPCR\DocumentManager');
        $this->managerRegistry = $this->prophesize('Doctrine\Common\Persistence\ManagerRegistry');
        $this->finder = $this->prophesize('DTL\Glob\FinderInterface');
        $this->uow = $this->prophesize('Doctrine\ODM\PHPCR\UnitOfWork');
        $this->document = new \stdClass;

        $this->managerRegistry->getManager()->willReturn($this->documentManager);
        $this->documentManager->getUnitOfWork()->willReturn($this->uow->reveal());

        $this->object = new \stdClass();
    }

    public function provideGet()
    {
        return array(
            array(null, '/cmf/foobar', '/cmf/foobar'),
            array('/site/foo.com', '/cmf/foobar', '/site/foo.com/cmf/foobar'),
            array('/site/foo.com', '/../foobar', '/site/foobar'),
        );
    }

    /**
     * @dataProvider provideGet
     */
    public function testGet($basePath, $requestedPath, $evaluatedPath)
    {
        $this->documentManager->find(null, $evaluatedPath)->willReturn($this->object);

        $res = $this->getRepository($basePath)->get($requestedPath);

        $this->assertInstanceOf('Symfony\Cmf\Component\Resource\Repository\Resource\PhpcrOdmResource', $res);
        $this->assertSame($this->object, $res->getDocument());
    }

    public function provideGetInvalid()
    {
        return array(
            array(null, 'cmf/foobar'),
            array(null, ''),
            array(null, new \stdClass),
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

    public function testFind()
    {
        $this->documentManager->find(null, '/cmf/foobar')->willReturn($this->document);
        $this->uow->getDocumentId($this->document)->willReturn('/cmf/foobar');

        $this->finder->find('/cmf/*')->willReturn(array(
            $this->document
        ));

        $res = $this->getRepository()->find('/cmf/*');

        $this->assertInstanceOf('Puli\Repository\Resource\Collection\ArrayResourceCollection', $res);
        $this->assertCount(1, $res);
        $documentResource = $res->offsetGet(0);
            ;
        $this->assertSame($this->document, $documentResource->getDocument());
    }

    protected function getRepository($path = null)
    {
        $repository = new PhpcrOdmRepository($this->managerRegistry->reveal(), $path, $this->finder->reveal());
        return $repository;
    }
}
