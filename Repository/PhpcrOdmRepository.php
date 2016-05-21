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
use PHPCR\Util\PathHelper;
use Puli\Repository\Api\ResourceCollection;
use Symfony\Cmf\Component\Resource\Repository\Resource\CmfResource;
use Symfony\Cmf\Component\Resource\Repository\Resource\PhpcrOdmResource;
use Puli\Repository\Resource\Collection\ArrayResourceCollection;
use Puli\Repository\Api\ResourceNotFoundException;
use Webmozart\Assert\Assert;
use Webmozart\PathUtil\Path;

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
        $parentPath = PathHelper::getParentPath($resolvedPath);
        $parentDocument = $this->getManager()->find(null, $parentPath);

        if (null === $parentDocument) {
            throw new InvalidArgumentException(sprintf('Cannot locate parent document at "%s"', $parentPath));
        }

        /** @var PhpcrOdmResource[] $resources */
        $resources = $resource instanceof IteratorAggregate ? $resource : new ArrayResourceCollection([$resource]);
        Assert::isInstanceOf($resources, ResourceCollection::class, 'The list should be of instance "ResourceCollection".');

        foreach ($resources as $resource) {
            Assert::isInstanceOf($resource, CmfResource::class, 'The resource needs to of instance "CmfResource".');
            Assert::notNull($resource->getName(), 'The resource needs a name for the creation.');

            $document = $resource->getPayload();
            $document->setName($resource->getName());
            if ($document instanceof HierarchyInterface) {
                $document->setParentDocument($parentDocument);
            }
            $this->getManager()->persist($document);
        }

        $this->getManager()->flush();
    }

    /**
     * Moves a resource inside the repository.
     *
     * @param string $sourceQuery The Path of the current document.
     * @param string $targetPath  The parent path of the destination.
     * @param string $language
     *
     * @return int
     */
    public function move($sourceQuery, $targetPath, $language = 'glob')
    {
        $this->failUnlessGlob($language);
        Assert::notEq('', trim($sourceQuery, '/'), 'The root directory cannot be moved.');

        $targetPath = $this->resolvePath($targetPath);
        $sourcePath = $this->resolvePath($sourceQuery);

        $document = $this->getManager()->find(null, $sourcePath);
        if (null === $document) {
            throw new \InvalidArgumentException('No document found for source path '.$sourcePath);
        }
        $targetParentDocument = $this->getManager()->find(null, $targetPath);
        if (null === $targetParentDocument) {
            throw new \InvalidArgumentException('No parent document found for target path '.$targetPath);
        }

        if ($document instanceof HierarchyInterface) {
            $document->setParentDocument($targetParentDocument);

            $this->getManager()->persist($document);
            $this->getManager()->flush();
            
            return 1;
        }

        return 0;
    }

    /**
     * {@inheritdoc}
     */
    public function clear()
    {
        // TODO: Implement clear() method.
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
}
