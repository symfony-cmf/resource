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

/**
 * Abstract repository for both PHPCR and PHPCR-ODM repositories
 *
 * @author Daniel Leech <daniel@dantleech.com>
 */
abstract class AbstractPhpcrRepository implements ResourceRepository
{
    /**
     * Base path from which to serve nodes / documents
     *
     * @var string $basePath
     */
    private $basePath;

    /**
     * @param string $basePath
     */
    public function __construct($basePath = null)
    {
        $this->basePath = $basePath;
    }

    /**
     * {@inheritDoc}
     */
    public function hasChildren($path)
    {
        return !empty($this->listChildren($path));
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
}
