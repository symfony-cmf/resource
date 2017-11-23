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

use DTL\Glob\FinderInterface;
use DTL\Glob\GlobHelper;
use Symfony\Cmf\Component\Resource\Puli\AbstractRepository;
use Symfony\Cmf\Component\Resource\Puli\Api\ResourceRepository;
use Symfony\Cmf\Component\Resource\Puli\ArrayResourceCollection;
use Symfony\Cmf\Component\Resource\Repository\Api\EditableRepository;
use Webmozart\Assert\Assert;
use Webmozart\PathUtil\Path;

/**
 * Abstract repository for both PHPCR and PHPCR-ODM repositories.
 *
 * @author Daniel Leech <daniel@dantleech.com>
 *
 * @internal
 */
abstract class AbstractPhpcrRepository extends AbstractRepository implements ResourceRepository, EditableRepository
{
    /**
     * Base path from which to serve nodes / nodes.
     *
     * @var string
     */
    private $basePath;

    /**
     * @var FinderInterface
     */
    private $finder;

    /**
     * @var GlobHelper
     */
    private $globHelper;

    /**
     * @param string $basePath
     */
    public function __construct(FinderInterface $finder, $basePath = null)
    {
        $this->finder = $finder;
        $this->basePath = $basePath;
        $this->globHelper = new GlobHelper();
    }

    /**
     * {@inheritdoc}
     */
    public function hasChildren($path)
    {
        $children = $this->listChildren($path);

        return (bool) count($children);
    }

    /**
     * {@inheritdoc}
     */
    public function find($query, $language = 'glob')
    {
        if ('glob' !== $language) {
            throw new \RuntimeException(sprintf(
                'The language "%s" is not supported.',
                $language
            ));
        }

        $nodes = $this->finder->find($this->resolvePath($query));

        return $this->buildCollection($nodes);
    }

    /**
     * {@inheritdoc}
     */
    public function remove($query, $language = 'glob')
    {
        $this->failUnlessGlob($language);
        $nodes = $this->finder->find($this->resolvePath($query));

        if (0 === count($nodes)) {
            return 0;
        }

        try {
            // delegate remove nodes to the implementation
            $this->removeNodes($nodes);
        } catch (\Exception $e) {
            throw new \RuntimeException(sprintf(
                'Error encountered when removing resource(s) using query "%s"',
                $query
            ), null, $e);
        }

        return count($nodes);
    }

    /**
     * {@inheritdoc}
     */
    public function move($query, $targetPath, $language = 'glob')
    {
        $this->failUnlessGlob($language);
        $nodes = $this->finder->find($this->resolvePath($query));

        if (0 === count($nodes)) {
            return 0;
        }

        $targetPath = $this->resolvePath($targetPath);

        try {
            // delegate moving to the implementation
            $this->moveNodes($nodes, $query, $targetPath);
        } catch (\Exception $e) {
            throw new \RuntimeException(sprintf(
                'Error encountered when moving resource(s) using query "%s"',
                $query
            ), null, $e);
        }

        return count($nodes);
    }

    /**
     * {@inheritdoc}
     */
    public function clear()
    {
        throw new \BadMethodCallException('Clear not supported');
    }

    /**
     * {@inheritdoc}
     */
    public function add($path, $resource)
    {
        throw new \BadMethodCallException('Add not supported');
    }

    /**
     * {@inheritdoc}
     */
    public function reorder($sourcePath, $position)
    {
        Assert::greaterThanEq($position, 0, 'Reorder position cannot be negative, got: %s');
        $this->reorderNode($sourcePath, $position);
    }

    /**
     * Return the path with the basePath prefix
     * if it has been set.
     *
     * @param string $path
     *
     * @return string
     */
    public function resolvePath($path)
    {
        $path = $this->sanitizePath($path);

        if ($this->basePath) {
            $path = $this->basePath.$path;
        }

        $path = Path::canonicalize($path);

        return $path;
    }

    /**
     * Remove the base prefix from the given path.
     *
     * @param string $path
     *
     * @return string
     */
    protected function unresolvePath($path)
    {
        $path = substr($path, strlen($this->basePath));

        return $path;
    }

    protected function isGlobbed($string)
    {
        return $this->globHelper->isGlobbed($string);
    }

    /**
     * Build a collection of PHPCR resources.
     *
     * @return ArrayResourceCollection
     */
    abstract protected function buildCollection(array $nodes);

    /**
     * Remove the given nodes.
     *
     * @see EditableRepository::remove()
     *
     * @param NodeInterface[]
     */
    abstract protected function removeNodes(array $nodes);

    /**
     * Move the given nodes.
     *
     * @see EditableRepository::move()
     *
     * @param NodeInterface[]
     */
    abstract protected function moveNodes(array $nodes, $query, $targetPath);

    /**
     * Reorder the node at the given path to be in $position position.
     *
     * @see EditableRepository::reorder()
     *
     * @param string $sourcePath
     * @param int    $position
     */
    abstract protected function reorderNode($sourcePath, $position);
}
