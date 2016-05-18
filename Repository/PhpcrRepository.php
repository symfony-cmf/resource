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

use PHPCR\SessionInterface;
use DTL\Glob\Finder\PhpcrTraversalFinder;
use DTL\Glob\FinderInterface;
use Puli\Repository\Api\ChangeStream\VersionList;
use Symfony\Cmf\Component\Resource\Repository\Resource\PhpcrResource;
use Puli\Repository\Resource\Collection\ArrayResourceCollection;
use Puli\Repository\Api\ResourceNotFoundException;

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
     * Returns all versions of a resource.
     *
     * @param string $path The path to the resource.
     *
     * @return VersionList The versions stored for this path.
     *
     * @throws NoVersionFoundException  If no version can be found.
     * @throws InvalidArgumentException If the path is invalid. The path must
     *                                  be a non-empty string starting with "/".
     */
    public function getVersions($path)
    {
        return new VersionList($path, []);
    }
}
