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

use InvalidArgumentException;
use Puli\Repository\Api\EditableRepository;
use Puli\Repository\Api\UnsupportedLanguageException;

/**
 * CMF own interface to add the move() method.
 *
 * @author Maximilian Berghoff <Maximilian.Berghoff@mayflower.de>
 */
interface CmfEditableRepository extends EditableRepository
{
    /**
     * Moves a resource inside the repository.
     *
     * @param string $sourceQuery The Path of the current document.
     * @param string $targetPath  The parent path of the destination.
     * @param string $language
     *
     * @return int
     *
     * @throws InvalidArgumentException     If the sourceQuery is invalid.
     * @throws InvalidArgumentException     If the resource can not be moved to the targetPath.
     * @throws UnsupportedLanguageException If the language is not supported.
     */
    public function move($sourceQuery, $targetPath, $language = 'glob');
}
