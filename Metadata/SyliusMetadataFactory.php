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

use Symfony\Cmf\Component\Resource\Repository\PayloadMetadataFactoryInterface;
use Symfony\Cmf\Component\Resource\Repository\Resource\CmfResource;
use Symfony\Cmf\Component\Resource\Repository\PayloadMetadata;

/**
 * Add links and meta-info from Sonata Admin
 *
 * @author Daniel Leech <daniel@dantleech.com>
 */
class SyliusMetadataFactory implements PayloadMetadataFactoryInterface
{
    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var UrlGeneratorInterface
     */
    private $urlGenerator;

    /**
     * __construct
     *
     * @param Registry $registry
     * @param UrlGeneratorInterface $urlGenerator
     */
    public function __construct(Registry $registry, UrlGeneratorInterface $urlGenerator)
    {
        $this->registry = $registry;
        $this->urlGenerator = $urlGenerator;
    }

    /**
     * {@inheritDoc}
     */
    public function get(CmfResource $resource)
    {
        $object = $resource->getPayload();

        // sonata has dependency on ClassUtils so this is fine.
        $class = ClassUtils::getClass($object);

        // TODO: does this already throw an exception?
        $metadata = $this->registry->getMetadataForClass($class);

        $metadata = new PayloadMetadata(
            'sonata',
            [
                PayloadMetadata::ALIAS => $metadata->getAlias(),
                // PayloadMetadata::TITLE => TODO: Is this supported?
                PayloadMetadata::TYPE_TITLE => $metadata->getAlias(), // maybe run this through the translator

                // TODO: Links are by convention
                PayloadMetadata::LINK_EDIT_HTML => null,
                PayloadMetadata::LINK_UPDATE_HTML => null, 
                PayloadMetadata::LINK_CREATE_HTML => null, 
                PayloadMetadata::LINK_REMOVE_HTML => null, 
            ]
        );

        return $metadata;
    }
}
