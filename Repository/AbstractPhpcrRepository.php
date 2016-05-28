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
use Puli\Repository\Api\ChangeStream\VersionList;
use Puli\Repository\Api\EditableRepository;
use Puli\Repository\Api\NoVersionFoundException;
use Puli\Repository\Api\ResourceNotFoundException;
use Puli\Repository\Api\ResourceRepository;
use Puli\Repository\Api\UnsupportedLanguageException;
use Puli\Repository\Resource\Collection\ArrayResourceCollection;
use Webmozart\Assert\Assert;
use Webmozart\PathUtil\Path;

/**
 * Abstract repository for both PHPCR and PHPCR-ODM repositories.
 *
 * @author Daniel Leech <daniel@dantleech.com>
 */
abstract class AbstractPhpcrRepository implements ResourceRepository, EditableRepository
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
     * Return the path with the basePath prefix
     * if it has been set.
     *
     * @param string $path
     *
     * @return string
     */
    protected function resolvePath($path)
    {
        Assert::stringNotEmpty($path, 'The path must be a non-empty string. Got: %s');
        Assert::startsWith($path, '/', 'The path %s is not absolute.');

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
     * {@inheritdoc}
     */
    public function getVersions($path)
    {
        try {
            return new VersionList($path, [$this->get($path)]);
        } catch (ResourceNotFoundException $e) {
            throw NoVersionFoundException::forPath($path, $e);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function remove($query, $language = 'glob')
    {
        $this->failUnlessGlob($language);

        Assert::startsWith($query, '/', 'The target path %s is not absolute.');
        Assert::notEq('', trim($query, '/'), 'The root directory cannot be deleted.');
        $resolvedPath = $this->resolvePath($query);

        return $this->removeResource($resolvedPath);
    }

    /**
     * Validate a language is usable to search in repositories.
     *
     * @param string $language
     */
    protected function failUnlessGlob($language)
    {
        if ('glob' !== $language) {
            throw UnsupportedLanguageException::forLanguage($language);
        }
    }

    /**
     * Will finally remove the resource.
     *
     * @param string $sourcePath
     *
     * @return int
     */
    abstract protected function removeResource($sourcePath);
}
