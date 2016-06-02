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

use DTL\Glob\FinderInterface;
use Puli\Repository\Api\ResourceRepository;
use Puli\Repository\Api\UnsupportedLanguageException;
use Puli\Repository\Resource\Collection\ArrayResourceCollection;
use Webmozart\Assert\Assert;
use Webmozart\PathUtil\Path;
use Puli\Repository\AbstractRepository;
use Symfony\Cmf\Component\Resource\Repository\Api\EditableRepository;

/**
 * Abstract repository for both PHPCR and PHPCR-ODM repositories.
 *
 * @author Daniel Leech <daniel@dantleech.com>
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
     * @param string $basePath
     */
    public function __construct(FinderInterface $finder, $basePath = null)
    {
        $this->finder = $finder;
        $this->basePath = $basePath;
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
        if ($language != 'glob') {
            throw new UnsupportedLanguageException($language);
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
        Assert::notEq('', trim($query, '/'), 'The root directory cannot be deleted.');
        $nodes = $this->finder->find($this->resolvePath($query));

        // delegate remove nodes to the implementation
        $this->removeNodes($nodes);
    }

    /**
     * {@inheritdoc}
     */
    public function move($sourceQuery, $targetPath, $language = 'glob')
    {
        $this->failUnlessGlob($language);
        Assert::notEq('', trim($sourceQuery, '/'), 'The root directory cannot be moved.');
        $nodes = $this->finder->find($this->resolvePath($query));

        $this->moveNodes($nodes, $sourceQuery, $targetPath);
    }

    /**
     * {@inheritdoc}
     */
    public function clear()
    {
        throw new \BadMethodCallException('Clear not supported');
    }

    /**
     * Return the path with the basePath prefix
     * if it has been set.
     *
     * @param string $path
     *
     * @return string
     */
    protected function resolvePath($path)
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

    /**
     * Build a collection of PHPCR resources.
     *
     * @return ArrayResourceCollection
     */
    abstract protected function buildCollection(array $nodes);

    /**
     * Rmeove the given nodes.
     *
     * @param NodeInterface[]
     */
    abstract protected function removeNodes($nodes);

    /**
     * Move the given nodes.
     *
     * @param NodeInterface[]
     */
    abstract protected function moveNodes($nodes, $sourceQuery, $targetPath);
}
