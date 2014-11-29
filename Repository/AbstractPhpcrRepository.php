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

use Puli\Repository\ResourceRepositoryInterface;
use Doctrine\Common\Persistence\ManagerRegistry;
use Puli\Repository\ResourceNotFoundException;
use Symfony\Cmf\Component\Resource\ObjectResource;
use Symfony\Cmf\Component\Resource\FinderInterface;
use Puli\Resource\Collection\ResourceCollection;
use Puli\Repository\InvalidPathException;
use Puli\Util\Path;

/**
 * Abstract repository for both PHPCR and PHPCR-ODM repositories
 *
 * @author Daniel Leech <daniel@dantleech.com>
 */
abstract class AbstractPhpcrRepository implements ResourceRepositoryInterface
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
     * Return the path with the basePath prefix
     * if it has been set.
     *
     * @param string $path
     *
     * @return string
     */
    protected function getPath($path)
    {
        if ('' === $path) {
            throw new InvalidPathException('The path must not be empty.');
        }

        if (!is_string($path)) {
            throw new InvalidPathException(sprintf(
                'The path must be a string. Is: %s.',
                is_object($path) ? get_class($path) : gettype($path)
            ));
        }

        if ($this->basePath) {
            $path = $this->basePath . $path;
        }

        $path = Path::canonicalize($path);

        if ('/' !== $path[0]) {
            throw new InvalidPathException(sprintf(
                'The path "%s" is not absolute.',
                $path
            ));
        }

        return $path;
    }
}

