<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2017 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Component\Resource\Puli;

use Symfony\Cmf\Component\Resource\Puli\Api\ResourceRepository;
use Webmozart\Assert\Assert;
use Webmozart\PathUtil\Path;

/**
 * Abstract base for repositories providing tools to avoid code duplication.
 *
 * @since  1.0
 *
 * @author Bernhard Schussek <bschussek@gmail.com>
 * @author Titouan Galopin <galopintitouan@gmail.com>
 */
abstract class AbstractRepository implements ResourceRepository
{
    /**
     * Validate a language is usable to search in repositories.
     *
     * @param string $language
     */
    protected function failUnlessGlob($language)
    {
        if ('glob' !== $language) {
            throw new \RuntimeException(sprintf(
                'The language "%s" is not supported.',
                $language
            ));
        }
    }

    /**
     * Sanitize a given path and check its validity.
     *
     * @param string $path
     *
     * @return string
     */
    protected function sanitizePath($path)
    {
        Assert::stringNotEmpty($path, 'The path must be a non-empty string. Got: %s');
        Assert::startsWith($path, '/', 'The path %s is not absolute.');

        return Path::canonicalize($path);
    }
}
