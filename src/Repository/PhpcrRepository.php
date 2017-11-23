<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2017 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Component\Resource\Repository;

use DTL\Glob\Finder\PhpcrTraversalFinder;
use DTL\Glob\FinderInterface;
use PHPCR\SessionInterface;
use Symfony\Cmf\Component\Resource\Puli\ArrayResourceCollection;
use Symfony\Cmf\Component\Resource\Repository\Resource\PhpcrResource;

/**
 * Resource repository for PHPCR.
 *
 * @author Daniel Leech <daniel@dantleech.com>
 *
 * @internal
 */
class PhpcrRepository extends AbstractPhpcrRepository
{
    /**
     * @var SessionInterface
     */
    private $session;

    /**
     * @param SessionInterface $session
     * @param string           $basePath
     * @param FinderInterface  $finder
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
        $node = $this->getNode($path);

        $resource = new PhpcrResource($path, $node);
        $resource->attachTo($this);

        return $resource;
    }

    /**
     * {@inheritdoc}
     */
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
        return [];
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
    protected function removeNodes(array $nodes)
    {
        foreach ($nodes as $node) {
            $node->remove();
        }

        $this->session->save();
    }

    /**
     * {@inheritdoc}
     */
    protected function moveNodes(array $nodes, $sourceQuery, $targetPath)
    {
        $this->doMoveNodes($nodes, $sourceQuery, $targetPath);
        $this->session->save();
    }

    /**
     * {@inheritdoc}
     */
    public function reorderNode($sourcePath, $position)
    {
        $node = $this->getNode($sourcePath);
        $parent = $node->getParent();
        $nodeNames = $parent->getNodeNames();

        if (0 === $position) {
            $parent->orderBefore($node->getName(), $nodeNames[$position]);
        } elseif (isset($nodeNames[$position + 1])) {
            $parent->orderBefore($node->getName(), $nodeNames[$position + 1]);
        } else {
            $lastName = $nodeNames[count($nodeNames) - 1];
            $parent->orderBefore($node->getName(), $lastName);
            $parent->orderBefore($lastName, $node->getName());
        }

        $this->session->save();
    }

    private function doMoveNodes(array $nodes, $sourceQuery, $targetPath)
    {
        if (false === $this->isGlobbed($sourceQuery)) {
            return $this->session->move(current($nodes)->getPath(), $targetPath);
        }

        foreach ($nodes as $node) {
            $this->session->move($node->getPath(), $targetPath.'/'.$node->getName());
        }
    }

    private function getNode($path)
    {
        $resolvedPath = $this->resolvePath($path);

        try {
            $node = $this->session->getNode($resolvedPath);
        } catch (\PHPCR\PathNotFoundException $e) {
            throw new \RuntimeException(sprintf(
                'No PHPCR node could be found at "%s"',
                $resolvedPath
            ), null, $e);
        }

        if (null === $node) {
            throw new \RuntimeException('Session did not return a node or throw an exception');
        }

        return $node;
    }
}
