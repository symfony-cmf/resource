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

use Puli\Repository\ResourceRepositoryInterface;
use Doctrine\Common\Persistence\ManagerRegistry;
use Puli\Repository\ResourceNotFoundException;
use Symfony\Cmf\Component\Resource\ObjectResource;
use Symfony\Cmf\Component\Resource\FinderInterface;
use Puli\Resource\Collection\ResourceCollection;

class PhpcrOdmRepository implements ResourceRepositoryInterface
{
    /**
     * @var ManagerRegistry
     */
    private $managerRegistry;

    /**
     * @var FinderInterface
     */
    private $finder;

    public function __construct(ManagerRegistry $managerRegistry, FinderInterface $finder)
    {
        $this->managerRegistry = $managerRegistry;
        $this->finder = $finder;
    }

    protected function getManager()
    {
        return $this->managerRegistry->getManager();
    }

    /**
     * {@inheritDoc}
     */
    public function get($path)
    {
        $document = $this->getManager()->find(null, $path);

        if (null === $document) {
            throw new ResourceNotFoundException(sprintf(
                'No PHPCR-ODM document could be found at "%s"',
                $path
            ));
        }

        $resource = new ObjectResource($path, $document);

        return $resource;
    }

    /**
     * {@inheritDoc}
     */
    public function find($selector)
    {
        $documents = $this->finder->find($selector);
        $collection = new ResourceCollection();

        if (!$documents) {
            return $collection;
        }

        $uow = $this->getManager()->getUnitOfWork();

        foreach ($documents as $document) {
            $path = $uow->getDocumentId($document);
            $collection->add(new ObjectResource($path, $document));
        }

        return $collection;
    }

    /**
     * {@inheritDoc}
     */
    public function contains($selector)
    {
        return count($this->find($selector)) > 0;
    }

    /**
     * {@inheritDoc}
     */
    public function findByTag($tag)
    {
        throw new \Exception('Get by tag not currently supported');
    }

    /**
     * {@inheritDoc}
     */
    public function getTags()
    {
        return array();
    }
}
