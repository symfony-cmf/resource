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
use DTL\Glob\Finder\PhpcrOdmTraversalFinder;
use DTL\Glob\FinderInterface;
use InvalidArgumentException;
use PHPCR\NodeInterface;
use PHPCR\Util\NodeHelper;
use Puli\Repository\Api\Resource\PuliResource;
use Puli\Repository\Api\ResourceCollection;
use Puli\Repository\Api\UnsupportedLanguageException;
use Puli\Repository\Api\UnsupportedResourceException;
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
     * Adds a new resource to the repository.
     *
     * All resources passed to this method must implement {@link PuliResource}.
     *
     * @param string $path The path at which to add the resource.
     * @param PuliResource|ResourceCollection $resource The resource(s) to add
     *                                                  at that path.
     *
     * @throws InvalidArgumentException     If the path is invalid. The path
     *                                      must be  a non-empty string starting
     *                                      with "/".
     * @throws UnsupportedResourceException If the resource is invalid.
     */
    public function add($path, $resource)
    {
        Assert::notEq('', trim($path, '/'), 'The root directory cannot be created.');
        Assert::startsWith($path, '/', 'The target path %s is not absolute.');

        $resolvedPath = $this->resolvePath($path);
        $document = $this->getManager()->find(null, $resolvedPath);

        if (null !== $document) {
            throw new InvalidArgumentException(sprintf('There still exists a resource in path %s', $path));
        }

        $parentNode = NodeHelper::createPath($this->getManager()->getPhpcrSession(), $path);
        if (!$parentNode instanceof NodeInterface) {
            throw new InvalidArgumentException('No parent node created for ' . $path);
        }

        if ($resource instanceof ArrayResourceCollection) {
            /** @var PhpcrOdmResource[] $resource */
            foreach ($resource as $item) {
                Assert::notNull($item->getName(), 'The resource needs a name for the creation');
                $document = $item->getPayload();
                $document->setName($item->getName());
                $document->setParent($parentNode);
                $this->getManager()->persist($document);
            }
        } elseif ($resource instanceof PhpcrOdmResource) {
            Assert::notNull($resource->getName(), 'The resource needs a name for the creation');

            $document = $resource->getPayload();
            $document->setName($resource->getName());
            $document->setParent($parentNode);
            $this->getManager()->persist($document);
        }

        $this->getManager()->flush();
    }

    /**
     * Removes all resources matching the given query.
     *
     * @param string $query A resource query.
     * @param string $language The language of the query. All implementations
     *                         must support the language "glob".
     *
     * @return int The number of resources removed from the repository.
     *
     * @throws InvalidArgumentException     If the query is invalid.
     * @throws UnsupportedLanguageException If the language is not supported.
     */
    public function remove($query, $language = 'glob')
    {
        // TODO: Implement remove() method.
    }

    /**
     * Removes all resources from the repository.
     *
     * @return int The number of resources removed from the repository.
     */
    public function clear()
    {
        // TODO: Implement clear() method.
    }
}
