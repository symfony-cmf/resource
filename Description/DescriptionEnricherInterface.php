<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2015 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Component\Resource\Description;

use Symfony\Cmf\Component\Resource\Repository\Resource\CmfResource;

interface DescriptionEnricherInterface
{
    /**
     * Enrich the payload description.
     *
     * @param Description $description
     * @param object      $payload
     *
     * @return Description
     */
    public function enrich(Description $description, $payload);

    /**
     * Return true if the provider supports the given type.
     *
     * @param CmfResource $resource
     *
     * @return bool
     */
    public function supports(CmfResource $resource);
}
