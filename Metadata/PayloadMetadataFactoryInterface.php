<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2015 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Component\Resource\Metadata;

use Symfony\Cmf\Component\Resource\Repository\Resource\CmfResource;

interface PayloadMetadataFactoryInterface
{
    /**
     * Return the metadata for the given payload.
     */
    public function getPayloadMetadata(CmfResource $resource);
}
