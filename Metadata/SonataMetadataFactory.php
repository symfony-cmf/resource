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
class SonataMetadataFactory implements PayloadMetadataFactoryInterface
{
    /**
     * @var Pool
     */
    private $pool;

    /**
     * @var UrlGeneratorInterface
     */
    private $urlGenerator;

    /**
     * __construct
     *
     * @param Pool $pool
     * @param UrlGeneratorInterface $urlGenerator
     */
    public function __construct(Pool $pool, UrlGeneratorInterface $urlGenerator)
    {
        $this->pool = $pool;
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

        if (false === $this->pool->hasAdminByClass($class)) {
            return $data;
        }

        $admin = $this->pool->getAdminByClass($class);

        $links = array();

        $routeCollection = $admin->getRoutes();

        foreach ($routeCollection->getElements() as $code => $route) {
            $routeName = $route->getDefault('_sonata_name');
            $url = $this->urlGenerator->generate($routeName, array(
                $admin->getIdParameter() => $admin->getUrlsafeIdentifier($object),
            ), true);

            $routeRole = substr($code, strlen($admin->getCode()) + 1);

            $links[$routeRole] = $url;
        }

        $metadata = new PayloadMetadata(
            'sonata',
            [
                PayloadMetadata::TITLE => $admin->toString($object),
                PayloadMetadata::TYPE_TITLE => $admin->getLabel(),
                PayloadMetadata::LINK_EDIT_HTML => $links['edit'],
                PayloadMetadata::LINK_UPDATE_HTML => $links['update'],
                PayloadMetadata::LINK_CREATE_HTML => $links['create'],
                PayloadMetadata::LINK_REMOVE_HTML => $links['remove'],
            ]
        );

        return $metadata;
    }
}
