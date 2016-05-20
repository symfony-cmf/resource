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

use InvalidArgumentException;
use IteratorAggregate;
use PHPCR\NodeInterface;
use PHPCR\SessionInterface;
use DTL\Glob\Finder\PhpcrTraversalFinder;
use DTL\Glob\FinderInterface;
use Puli\Repository\Api\ResourceCollection;
use Symfony\Cmf\Component\Resource\Repository\Resource\CmfResource;
use Symfony\Cmf\Component\Resource\Repository\Resource\PhpcrResource;
use Puli\Repository\Resource\Collection\ArrayResourceCollection;
use Puli\Repository\Api\ResourceNotFoundException;
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
        Assert::notEq('', trim($path, '/'), 'The root directory cannot be created.');
        Assert::startsWith($path, '/', 'The target path %s is not absolute.');

        $resolvedPath = $this->resolvePath($path);
        $parentNode = $this->session->getNode($resolvedPath);
        if (!$parentNode instanceof NodeInterface) {
            throw new InvalidArgumentException('No parent node created for '.$path);
        }

        /** @var PhpcrResource[] $resources */
        $resources = $resource instanceof IteratorAggregate ? $resource : new ArrayResourceCollection([ $resource ]);
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
     * {@inheritdoc}
     */
    protected function removeResource($sourcePath, $deleted)
    {
        $deleted += count($this->session->getNodes($sourcePath));

        $this->session->removeItem($sourcePath);
        $this->session->save();

        return $deleted;
    }

    /**
     * {@inheritdoc}
     */
    public function clear()
    {
        // TODO: Implement clear() method.
    }
}
