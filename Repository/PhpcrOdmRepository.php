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
use Puli\Resource\Collection\ResourceCollection;
use DTL\Glob\Finder\PhpcrOdmTraversalFinder;
use DTL\Glob\FinderInterface;

class PhpcrOdmRepository extends AbstractPhpcrRepository
{
    /**
     * @var ManagerRegistry
     */
    private $managerRegistry;

    /**
     * @var FinderInterface
     */
    private $finder;

    public function __construct(ManagerRegistry $managerRegistry, $basePath = null, FinderInterface $finder = null)
    {
        parent::__construct($basePath);
        $this->managerRegistry = $managerRegistry;
        $this->finder = $finder ? : new PhpcrOdmTraversalFinder($managerRegistry);
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
        $document = $this->getManager()->find(null, $this->getPath($path));

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
    public function find($query, $language = 'glob')
    {
        if ($language != 'glob') {
            throw new UnsupportedLanguageException($language);
        }

        $documents = $this->finder->find($selector);

        return $this->buildCollection($documents);
    }

    public function listChildren($path)
    {
        $document = $this->get($path);
        $children = $this->getManager()->getChildren($document);

        return $this->buildCollection($children);
    }

    /**
     * {@inheritDoc}
     */
    public function contains($selector, $language = 'glob')
    {
        return count($this->find($selector, $language)) > 0;
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

    private function buildCollection(array $documents)
    {
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
}
