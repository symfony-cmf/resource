<?php

namespace Symfony\Cmf\Component\Resource\Tests\Fixtures;
use Puli\Repository\Api\Resource\PuliResource;
use Puli\Repository\Api\ResourceRepository;

/**
 * @author Maximilian Berghoff <Maximilian.Berghoff@mayflower.de>
 */
class FalsyResource implements PuliResource
{
    /**
     * {@inheritdoc}
     */
    public function serialize()
    {
        // TODO: Implement serialize() method.
    }

    /**
     * {@inheritdoc}
     */
    public function unserialize($serialized)
    {
        // TODO: Implement unserialize() method.
    }

    /**
     * {@inheritdoc}
     */
    public function getPath()
    {
        // TODO: Implement getPath() method.
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        // TODO: Implement getName() method.
    }

    /**
     * {@inheritdoc}
     */
    public function getChild($relPath)
    {
        // TODO: Implement getChild() method.
    }

    /**
     * {@inheritdoc}
     */
    public function hasChild($relPath)
    {
        // TODO: Implement hasChild() method.
    }

    /**
     * {@inheritdoc}
     */
    public function hasChildren()
    {
        // TODO: Implement hasChildren() method.
    }

    /**
     * {@inheritdoc}
     */
    public function listChildren()
    {
        // TODO: Implement listChildren() method.
    }

    /**
     * {@inheritdoc}
     */
    public function getVersions()
    {
        // TODO: Implement getVersions() method.
    }

    /**
     * {@inheritdoc}
     */
    public function getMetadata()
    {
        // TODO: Implement getMetadata() method.
    }

    /**
     * {@inheritdoc}
     */
    public function getRepository()
    {
        // TODO: Implement getRepository() method.
    }

    /**
     * {@inheritdoc}
     */
    public function getRepositoryPath()
    {
        // TODO: Implement getRepositoryPath() method.
    }

    /**
     * {@inheritdoc}
     */
    public function attachTo(ResourceRepository $repo, $path = null)
    {
        // TODO: Implement attachTo() method.
    }

    /**
     * {@inheritdoc}
     */
    public function detach()
    {
        // TODO: Implement detach() method.
    }

    /**
     * {@inheritdoc}
     */
    public function isAttached()
    {
        // TODO: Implement isAttached() method.
    }

    /**
     * {@inheritdoc}
     */
    public function createReference($path)
    {
        // TODO: Implement createReference() method.
    }

    /**
     * {@inheritdoc}
     */
    public function isReference()
    {
        // TODO: Implement isReference() method.
    }
}
