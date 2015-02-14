<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2014 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Component\Resource\Repository\Resource;

use Puli\Repository\Resource\GenericResource;

/**
 * Base class for CMF resources.
 *
 * NOTE: This is not ideal and only exists here to add the "getPayloadType" method to the Puli API.
 *       See: https://github.com/puli/issues/issues/44
 */
class CmfResource extends GenericResource
{
    /**
     * Return the type of the payload.
     *
     * This could be any string which maps to the type
     * of the payload within the domain of the repository.
     *
     * For example, a PHPCR node type, or a FQCN.
     *
     * @return string|null
     */
    public function getPayloadType()
    {
        return null;
    }
}
