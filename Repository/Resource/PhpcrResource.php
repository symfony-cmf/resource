<?php

namespace Symfony\Cmf\Component\Resource\Repository\Resource;

use Puli\Repository\Api\Resource\Resource;
use Puli\Repository\Api\ResourceRepository;
use PHPCR\NodeInterface;
use Puli\Repository\Resource\Collection\ArrayResourceCollection;
use Symfony\Cmf\Component\Resource\Repository\Resource\Metadata\PhpcrMetadata;

/**
 * Resource representing a PHPCR node
 *
 * @author Daniel Leech <daniel@dantleech.com>
 */
class PhpcrResource implements Resource
{
    private $node;
    private $path;
    private $repoPath;

    /**
     * @param string $path
     * @param NodeInterface $node
     */
    public function __construct($path, NodeInterface $node)
    {
        $this->node = $node;
        $this->repoPath = $path;
        $this->path = $path;
    }

    /**
     * Return the PHPCR node which this resource
     * represents.
     *
     * @return NodeInterface
     */
    public function getNode()
    {
        return $this->node;
    }

    /**
     * {@inheritDoc}
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return $this->node->getName();
    }

    /**
     * {@inheritDoc}
     */
    public function getChild($relPath)
    {
        $child = $this->node->getNode($relPath);
        return new self($this->path . '/' . $relPath, $child);
    }

    /**
     * {@inheritDoc}
     */
    public function hasChild($relPath)
    {
        return $this->node->hasNode($relPath);
    }

    /**
     * {@inheritDoc}
     */
    public function hasChildren()
    {
        return $this->node->hasNodes();
    }

    /**
     * {@inheritDoc}
     */
    public function listChildren()
    {
        $collection = new ArrayResourceCollection();
        foreach ($this->node->getNodes() as $node) {
            $collection->add(new self($this->path . '/' . $node->getName(), $node));
        }

        return $collection;
    }

    /**
     * {@inheritDoc}
     */
    public function getMetadata()
    {
        return new PhpcrMetadata($this->node);
    }

    /**
     * {@inheritDoc}
     */
    public function getRepository()
    {
    }

    /**
     * {@inheritDoc}
     */
    public function getRepositoryPath()
    {
        return $this->repoPath;
    }

    /**
     * {@inheritDoc}
     */
    public function attachTo(ResourceRepository $repo, $path = null)
    {
    }

    /**
     * {@inheritDoc}
     */
    public function detach()
    {
    }

    /**
     * {@inheritDoc}
     */
    public function isAttached()
    {
    }

    /**
     * {@inheritDoc}
     */
    public function createReference($path)
    {
        $ref = clone $this;
        $ref->path = $path;

        return $ref;
    }

    /**
     * {@inheritDoc}
     */
    public function isReference()
    {
        return $this->path != $this->repoPath;
    }

    /**
     * {@inheritDoc}
     */
    public function serialize()
    {
    }

    /**
     * {@inheritDoc}
     */
    public function unserialize($string)
    {
    }
}
