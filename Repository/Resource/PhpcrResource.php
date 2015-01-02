<?php

namespace Symfony\Cmf\Component\Resource\Repository\Resource;

use Puli\Repository\Api\Resource\Resource;
use PHPCR\NodeInterface;
use Puli\Repository\Resource\Collection\ArrayResourceCollection;
use Symfony\Cmf\Component\Resource\Repository\Resource\Metadata\PhpcrMetadata;
use Puli\Repository\Resource\GenericResource;

/**
 * Resource representing a PHPCR node
 *
 * @author Daniel Leech <daniel@dantleech.com>
 */
class PhpcrResource extends GenericResource
{
    private $node;

    /**
     * @param string        $path
     * @param NodeInterface $node
     */
    public function __construct($path, NodeInterface $node)
    {
        parent::__construct($path);
        $this->node = $node;
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

        return new self($this->getPath().'/'.$relPath, $child);
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
            $collection->add(new self($this->getPath() . '/' . $node->getName(), $node));
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
}
