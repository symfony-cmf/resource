<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2014 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Component\Resource\Repository;

use Puli\Repository\Api\ResourceRepository;
use Webmozart\PathUtil\Path;
use Puli\Repository\Assert\Assertion;
use DTL\Glob\FinderInterface;

/**
 * Abstract repository for both PHPCR and PHPCR-ODM repositories
 *
 * @author Daniel Leech <daniel@dantleech.com>
 */
abstract class AbstractPhpcrRepository implements ResourceRepository
{
    /**
     * Base path from which to serve nodes / nodes
     *
     * @var string $basePath
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
     * {@inheritDoc}
     */
    public function hasChildren($path)
    {
        $children = $this->listChildren($path);

        return (boolean) count($children);
    }

    /**
     * {@inheritDoc}
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
     * Return the path with the basePath prefix
     * if it has been set.
     *
     * @param string $path
     *
     * @return string
     */
    protected function resolvePath($path)
    {
        Assertion::path($path);

        if ($this->basePath) {
            $path = $this->basePath . $path;
        }

        $path = Path::canonicalize($path);

        return $path;
    }

    /**
     * Remove the base prefix from the given path
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
     * Build a collection of PHPCR resources
     *
     * @return ArrayResourceCollection
     */
    abstract protected function buildCollection(array $nodes);
}
