<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2017 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Component\Resource\Description;

use Symfony\Cmf\Component\Resource\Puli\Api\PuliResource;

/**
 * @internal
 */
class DescriptionFactory
{
    /**
     * @var DescriptionEnhancerInterface[]
     */
    private $enhancers = [];

    /**
     * @param array $enhancers
     */
    public function __construct(array $enhancers)
    {
        $this->enhancers = $enhancers;
    }

    /**
     * Return a description of the given (CMF) Resource.
     *
     * @param PuliResource $resource
     *
     * @return Description
     */
    public function getPayloadDescriptionFor(PuliResource $resource)
    {
        $description = new Description($resource);

        foreach ($this->enhancers as $enhancer) {
            if (false === $enhancer->supports($resource)) {
                continue;
            }

            $enhancer->enhance($description);
        }

        return $description;
    }
}
