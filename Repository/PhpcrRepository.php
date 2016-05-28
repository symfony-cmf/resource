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

use DTL\Glob\Finder\PhpcrTraversalFinder;
use DTL\Glob\FinderInterface;
use InvalidArgumentException;
use IteratorAggregate;
use PHPCR\PathNotFoundException;
use PHPCR\SessionInterface;
use Puli\Repository\Api\ResourceCollection;
use Puli\Repository\Api\ResourceNotFoundException;
use Puli\Repository\Resource\Collection\ArrayResourceCollection;
use Symfony\Cmf\Component\Resource\Repository\Resource\CmfResource;
use Symfony\Cmf\Component\Resource\Repository\Resource\PhpcrResource;
use Webmozart\Assert\Assert;

/**
 * Resource repository for PHPCR.
 *
 * @author Daniel Leech <daniel@dantleech.com>
 */
class PhpcrRepository extends AbstractPhpcrRepository
{
    /**
     * @var ManagerRegistry
     */
    private $session;

    /**
     * @param SessionInterface $session
     * @param FinderInterface  $finder
     * @param string           $basePath
     */
    public function __construct(SessionInterface $session, $basePath = null, FinderInterface $finder = null)
    {
        $finder = $finder ?: new PhpcrTraversalFinder($session);
        parent::__construct($finder, $basePath);
        $this->session = $session;
    }

    /**
     * {@inheritdoc}
     */
    public function get($path)
    {
        $resolvedPath = $this->resolvePath($path);

        try {
            $node = $this->session->getNode($resolvedPath);
        } catch (\PHPCR\PathNotFoundException $e) {
            throw new ResourceNotFoundException(sprintf(
                'No PHPCR node could be found at "%s"',
                $resolvedPath
            ), null, $e);
        }

        if (null === $node) {
            throw new \RuntimeException('Session did not return a node or throw an exception');
        }

        $resource = new PhpcrResource($path, $node);
        $resource->attachTo($this);

        return $resource;
    }

    public function listChildren($path)
    {
        $resource = $this->get($path);

        return $this->buildCollection((array) $resource->getPayload()->getNodes());
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
    protected function buildCollection(array $nodes)
    {
        $collection = new ArrayResourceCollection();

        if (!$nodes) {
            return $collection;
        }

        foreach ($nodes as $node) {
            $path = $this->unresolvePath($node->getPath());
            $resource = new PhpcrResource($path, $node);
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
        Assert::startsWith($path, '/', 'Target path %s must be absolute.');
        Assert::notEq('', trim($path, '/'), 'The root directory cannot be created.');

        $resolvedPath = $this->resolvePath($path);
        try {
            $parentNode = $this->session->getNode($resolvedPath);
        } catch (PathNotFoundException $e) {
            throw new InvalidArgumentException('No parent node created for '.$path, $e->getCode(), $e);
        }

        /** @var PhpcrResource[] $resources */
        $resources = $resource instanceof IteratorAggregate ? $resource : new ArrayResourceCollection([$resource]);
        Assert::isInstanceOf($resources, ResourceCollection::class, 'The list should be of instance "ResourceCollection".');

        foreach ($resources as $resource) {
            Assert::isInstanceOf($resource, CmfResource::class, 'The resource needs to of instance "CmfResource".');
            Assert::notNull($resource->getName(), 'The resource needs a name for the creation.');
            Assert::notNull($resource->getPayloadType(), 'The resource needs a type for the creation');

            $parentNode->addNode($resource->getName(), $resource->getPayloadType());
        }

        $this->session->save();
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

        try {
            $this->session->move($sourcePath, $targetPath);

            return 1;
        } catch (PathNotFoundException $e) {
            throw new \InvalidArgumentException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function clear()
    {
        throw new \BadMethodCallException('Clear currently not supported');
    }

    /**
     * {@inheritdoc}
     */
    protected function removeResource($sourcePath)
    {
        $deleted = count($this->session->getNode($sourcePath)->getNodes()) + 1;

        try {
            $this->session->removeItem($sourcePath);
        } catch (PathNotFoundException $e) {
            throw new \InvalidArgumentException(
                sprintf('Could not remove PHPCR resource at "%s"', $sourcePath),
                $e->getCode(),
                $e
            );
        }
        $this->session->save();

        return $deleted;
    }
}
