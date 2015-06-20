<?php

namespace Symfony\Cmf\Component\Resource\Repository;

use InvalidArgumentException;
use Puli\Repository\Api\ResourceCollection;
use Puli\Repository\Api\ResourceNotFoundException;
use Puli\Repository\Api\ResourceRepository;
use Puli\Repository\Resource\Collection\ArrayResourceCollection;
use Webmozart\PathUtil\Path;
use Webmozart\Assert\Assert;
use Puli\Repository\InMemoryRepository;

/**
 * Composite repository.
 *
 * Aggregates multiple repositories into one.
 *
 * - Repositories can be mounted at "/" or as immediate children of "/".
 * - Repositories can be mounted within other repositories.
 *
 * ````
 * $compositeRepository->mount('/', $repository1);
 * $compositeRepository->mount('/foo', $repository2);
 * $resources = $compositeRepository->listChildren('/');
 * var_dump($resource->getPaths()); // will list all children of repository 1 and also a /foo node
 * ````
 *
 * In the case that a mounted repository conflicts with a resource in an existing repository,
 * the mounted repository will take precedence.
 */
class CompositeRepository implements ResourceRepository
{
    private $repos = array();

    /**
     * Mount a repository at the given path.
     *
     * The path should be either "/" or an immediate child of "/" or
     * an immediate child of a resource in an existing mounted repository.
     *
     * @param string $path
     * @param ResourceRepository $repository
     */
    public function mount($path, ResourceRepository $repository)
    {
        $this->repos[$path] = $repository;
    }

    /**
     * {@inheritDoc}
     */
    public function get($path)
    {
        list($repository, $repoPath, $path) = $this->getRepository($path);

        return $repository->get($path)->createReference($repoPath.$path);
    }

    /**
     * {@inheritDoc}
     */
    public function find($query, $language = 'glob')
    {
        list($repository, $repoPath, $query) = $this->getRepository($query);
        return $this->replaceByReferences($repository->find($query), $repoPath);
    }

    /**
     * {@inheritDoc}
     */
    public function contains($query, $language = 'glob')
    {
        return $this->find($query, $language)->count() ? true : false;
    }

    /**
     * {@inheritDoc}
     */
    public function hasChildren($path)
    {
        return $this->listChildren($path)->count() ? true : false;
    }

    /**
     * {@inheritDoc}
     */
    public function listChildren($path)
    {
        list($repository, $repoPath, $path) = $this->getRepository($path);
        return $this->replaceByReferences($repository->listChildren($path), $repoPath);
    }

    /**
     * Returns a tuple containing the resolved repository, repository path
     * (mount point) and the resolved path.
     *
     * In case no repository was found for the given path, or the path is "/"
     * and no repostory is mounted at "/", an InMemoryRepository will be returned
     * containing references to all the root nodes of mounted repositories as children.
     *
     * @param string $path
     *
     * @return array
     */
    private function getRepository($path)
    {
        if ($path === '/') {
            return $this->getMemoryRepository($path);
        }

        $resolvedRepository = null;
        $resolvedPath = null;
        foreach ($this->repos as $repoPath => $repository) {
            if (0 !== strpos($repoPath, $path)) {
                continue;
            }

            if (null === $resolvedRepository || strlen($repoPath) > strlen($resolvedPath)) {
                $resolvedRepository = $repository;
                $resolvedPath = $repoPath;
            }
        }

        if (null !== $resolvedRepository) {
            return array($resolvedRepository, $resolvedPath, '/' . substr($path, strlen($resolvedPath)));
        }

        return $this->getMemoryRepository($path);
    }

    /**
     * Return an in-memory repository
     *
     * @see CompositeRepository:getRepository()
     * @param string $path
     *
     * @return array
     */
    private function getMemoryRepository($path)
    {
        // if we did not find amount point return an in memory repository
        // listing the root nodes of all mount points as children
        $memoryRepository = new InMemoryRepository();

        foreach ($this->repos as $repoPath => $repository) {
            if (substr_count($repoPath, '/') > 1) {
                continue;
            }

            $rootResource = $repository->get('/');
            $memoryRepository->add(
                $repoPath, $rootResource
            );
        }

        return array($memoryRepository, '/', $path);
    }

    /**
     * Replaces all resources in the collection by references.
     *
     * If a resource "/resource" was loaded from a mount point "/mount", the
     * resource is replaced by a reference with the path "/mount/resource".
     *
     * @param ResourceCollection $resources  The resources to replace.
     * @param string             $mountPoint The mount point from which the
     */
    private function replaceByReferences(ResourceCollection $resources, $mountPoint)
    {
        if ($mountPoint === '/') {
            return $resources;
        }
        foreach ($resources as $key => $resource) {
            $resources[$key] = $resource->createReference($mountPoint.$resource->getPath());
        }

        return $resources;
    }
}
