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

class DescriptionFactory
{
    private $enrichers = [];

    /**
     * @param array $enrichers
     */
    public function __construct(array $enrichers)
    {
        $this->enrichers = $enrichers;
    }

    /**
     * Return a description of the given (CMF) Resource.
     *
     * @param CmfResource $resource
     */
    public function getPayloadDescriptionFor(CmfResource $resource)
    {
        $type = $resource->getPayloadType();
        $payload = $resource->getPayload();
        $description = new Description($type);

        foreach ($this->enrichers as $enricher) {
            if (false === $enricher->supports($resource)) {
                continue;
            }

            $enricher->enrich($description, $payload);
        }

        return $description;
    }
}
