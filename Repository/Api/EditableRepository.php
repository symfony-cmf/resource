<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2015 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Component\Resource\Repository\Api;

use InvalidArgumentException;
use Puli\Repository\Api\EditableRepository;
use Puli\Repository\Api\UnsupportedLanguageException;

/**
 * Extends the Puli editable repository to implement the as-of-yet not
 * implemented features.
 *
 * @author Maximilian Berghoff <Maximilian.Berghoff@mayflower.de>
 */
interface EditableRepository extends EditableRepository
{
    /**
     * Move all resources found by $sourceQuery to the target (parent) path.
     *
     * @param string $sourceQuery
     * @param string $targetPath
     * @param string $language
     *
     * @throws InvalidArgumentException     If the sourceQuery is invalid.
     * @throws UnsupportedLanguageException If the language is not supported.
     */
    public function move($sourceQuery, $targetPath, $language = 'glob');
}
