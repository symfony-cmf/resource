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
use Symfony\Cmf\Component\Resource\Repository\PhpcrOdmRepository;

class PhpcrOdmRepositoryTest extends ProphecyTestCase
{
    public function setUp()
    {
        $this->documentManager = $this->prophesize('Doctrine\ODM\PHPCR\DocumentManager');
        $this->managerRegistry = $this->prophesize('Doctrine\Common\Persistence\ManagerRegistry');
        $this->finder = $this->prophesize('Symfony\Cmf\Component\Resource\FinderInterface');
        $this->uow = $this->prophesize('Doctrine\ODM\PHPCR\UnitOfWork');
        $this->document = new \stdClass;

        $this->managerRegistry->getManager()->willReturn($this->documentManager);
        $this->documentManager->getUnitOfWork()->willReturn($this->uow->reveal());

        $this->repository = new PhpcrOdmRepository($this->managerRegistry->reveal(), $this->finder->reveal());
        $this->object = new \stdClass();
    }

    public function testGet()
    {
        $this->documentManager->find(null, '/cmf/foobar')->willReturn($this->object);

        $res = $this->repository->get('/cmf/foobar');

        $this->assertInstanceOf('Symfony\Cmf\Component\Resource\ObjectResource', $res);
        $this->assertSame($this->object, $res->getObject());
    }

    public function testFind()
    {
        $this->documentManager->find(null, '/cmf/foobar')->willReturn($this->document);
        $this->uow->getDocumentId($this->document)->willReturn('/cmf/foobar');

        $this->finder->find('/cmf/*')->willReturn(array(
            $this->document
        ));

        $res = $this->repository->find('/cmf/*');

        $this->assertInstanceOf('Puli\Resource\Collection\ResourceCollection', $res);
        $this->assertCount(1, $res);
        $documentResource = $res->offsetGet(0);
            ;
        $this->assertSame($this->document, $documentResource->getObject());
    }
}
