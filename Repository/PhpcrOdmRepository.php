<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2015 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Component\Resource\Repository;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ODM\PHPCR\DocumentManager;
use Doctrine\ODM\PHPCR\HierarchyInterface;
use DTL\Glob\Finder\PhpcrOdmTraversalFinder;
use DTL\Glob\FinderInterface;
use InvalidArgumentException;
use IteratorAggregate;
use PHPCR\NodeInterface;
use PHPCR\Util\NodeHelper;
use Puli\Repository\Api\ResourceCollection;
use Symfony\Cmf\Component\Resource\Repository\Resource\CmfResource;
use Symfony\Cmf\Component\Resource\Repository\Resource\PhpcrOdmResource;
use Puli\Repository\Resource\Collection\ArrayResourceCollection;
use Puli\Repository\Api\ResourceNotFoundException;
use Webmozart\Assert\Assert;

class PhpcrOdmRepository extends AbstractPhpcrRepository
{
    /**
     * @var ManagerRegistry
     */
    private $managerRegistry;

    public function __construct(ManagerRegistry $managerRegistry, $basePath = null, FinderInterface $finder = null)
    {
        $finder = $finder ?: new PhpcrOdmTraversalFinder($managerRegistry);
        parent::__construct($finder, $basePath);
        $this->managerRegistry = $managerRegistry;
    }

    /**
     * @return ObjectManager|DocumentManager
     */
    protected function getManager()
    {
        return $this->managerRegistry->getManager();
    }

    /**
     * {@inheritdoc}
     */
    public function get($path)
    {
        $resolvedPath = $this->resolvePath($path);
        $document = $this->getManager()->find(null, $resolvedPath);

        if (null === $document) {
            throw new ResourceNotFoundException(sprintf(
                'No PHPCR-ODM document could be found at "%s"',
                $resolvedPath
            ));
        }

        $resource = new PhpcrOdmResource($path, $document);
        $resource->attachTo($this);

        return $resource;
    }

    /**
     * {@inheritdoc}
     */
    public function listChildren($path)
    {
        $resource = $this->get($path);
        $children = $this->getManager()->getChildren($resource->getPayload());

        return $this->buildCollection($children->toArray());
    }

    /**
     * {@inheritdoc}
     */
    public function contains($selector, $language = 'glob')
    {
        return count($this->find($selector, $language)) > 0;
    }

    /**
     * {@inheritdoc}
     */
    public function findByTag($tag)
    {
        throw new \Exception('Get by tag not currently supported');
    }

    /**
     * {@inheritdoc}
     */
    public function getTags()
    {
        return array();
    }

    /**
     * {@inheritdoc}
     */
    protected function buildCollection(array $documents)
    {
        $collection = new ArrayResourceCollection();

        if (empty($documents)) {
            return $collection;
        }

        $uow = $this->getManager()->getUnitOfWork();

        foreach ($documents as $document) {
            $childPath = $uow->getDocumentId($document);
            $path = $this->unresolvePath($childPath);
            $resource = new PhpcrOdmResource($path, $document);
            $resource->attachTo($this);
            $collection->add($resource);
        }

        return $collection;
    }

    /**
     * {@inheritdoc}
     */
    public function add($path, $resource)
    {
        Assert::notEq('', trim($path, '/'), 'You cannot create a root node.');
        Assert::startsWith($path, '/', 'Target path "%s" must be absolute.');

        $resolvedPath = $this->resolvePath($path);
        $parentNode = NodeHelper::createPath($this->getManager()->getPhpcrSession(), $resolvedPath);
        if (!$parentNode instanceof NodeInterface) {
            throw new InvalidArgumentException('No parent node created for '.$path);
        }

        /** @var PhpcrOdmResource[] $resources */
        $resources = $resource instanceof IteratorAggregate ? $resource : new ArrayResourceCollection([ $resource ]);
        Assert::isInstanceOf($resources, ResourceCollection::class, 'The list should be of instance "ResourceCollection".');

        foreach ($resources as $resource) {
            Assert::isInstanceOf($resource, CmfResource::class, 'The resource needs to of instance "CmfResource".');
            Assert::notNull($resource->getName(), 'The resource needs a name for the creation.');

            $document = $resource->getPayload();
            $document->setName($resource->getName());
            if ($document instanceof HierarchyInterface) {
                $document->setParentDocument($parentNode);
            }
            $this->getManager()->persist($document);
        }

        $this->getManager()->flush();
    }

    /**
     * {@inheritdoc}
     */
    protected function removeResource($sourcePath, $deleted)
    {
        $document = $this->getManager()->find(null, $sourcePath);
        $children = $this->getManager()->getChildren($document);
        $deleted += count($children->toArray());

        $this->getManager()->remove($document);
        $this->getManager()->flush();

        return ++$deleted;
    }

    /**
     * {@inheritdoc}
     */
    public function clear()
    {
        // TODO: Implement clear() method.
    }
}
