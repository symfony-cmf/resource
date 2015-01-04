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

use Doctrine\Common\Persistence\ManagerRegistry;
use Puli\Repository\ResourceNotFoundException;
use DTL\Glob\Finder\PhpcrOdmTraversalFinder;
use DTL\Glob\FinderInterface;
use Symfony\Cmf\Component\Resource\Repository\Resource\PhpcrOdmResource;
use Puli\Repository\Resource\Collection\ArrayResourceCollection;

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
        $this->finder = $finder ?: new PhpcrOdmTraversalFinder($managerRegistry);
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
        $document = $this->getManager()->find(null, $this->resolvePath($path));

        if (null === $document) {
            throw new ResourceNotFoundException(sprintf(
                'No PHPCR-ODM document could be found at "%s"',
                $path
            ));
        }

        $resource = new PhpcrOdmResource($path, $document);

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

        $documents = $this->finder->find($query);

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

    /**
     * Build a collection of PHPCR resources
     *
     * @return ArrayResourceCollection
     */
    private function buildCollection(array $documents)
    {
        $collection = new ArrayResourceCollection();

        if (empty($documents)) {
            return $collection;
        }

        $uow = $this->getManager()->getUnitOfWork();

        foreach ($documents as $document) {
            $path = $this->unresolvePath($uow->getDocumentId($document));
            $resource = new PhpcrOdmResource($path, $document);
            $resource->attachTo($this);
            $collection->add($resource);
        }

        return $collection;
    }
}
