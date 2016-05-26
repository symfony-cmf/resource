<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2015 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Component\Resource\Description\Enhancer\Sylius;

use Sylius\Component\Resource\Metadata\RegistryInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Add descriptors from the Sylius Resource component.
 *
 * @author Daniel Leech <daniel@dantleech.com>
 */
class ResourceEnhancer implements DescriptionEnhancerInterface
{
    /**
     * @var UrlGeneratorInterface
     */
    private $urlGenerator;

    /**
     * @var Registry
     */
    private $registry;

    public function __construct(RegistryInterface $registry, UrlGeneratorInterface $urlGenerator)
    {
        $this->registry = $registry;
        $this->urlGenerator = $urlGenerator;
    }

    /**
     * {@inheritdoc}
     */
    public function enhance(Description $description)
    {
        $object = $description->getResource()->getPayload();
    }

    /**
     * {@inheritdoc}
     */
    public function supports(PuliResource $resource)
    {
        if (false === $resource instanceof CmfResource) {
            return false;
        }

        throw new \Exception('here');
    }
}
