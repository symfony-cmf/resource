<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2015 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Component\Resource\Description\Enhancer\Sonata;

use Sonata\AdminBundle\Admin\Pool;
use Doctrine\Common\Util\ClassUtils;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Cmf\Component\Resource\Description\DescriptionEnhancerInterface;
use Symfony\Cmf\Component\Resource\Description\Description;
use Puli\Repository\Api\Resource\PuliResource;
use Symfony\Cmf\Component\Resource\Description\Descriptor;

/**
 * Add links and meta-info from Sonata Admin.
 *
 * @author Daniel Leech <daniel@dantleech.com>
 */
class AdminEnhancer implements DescriptionEnhancerInterface
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
     * @param Pool                  $pool
     * @param UrlGeneratorInterface $urlGenerator
     */
    public function __construct(Pool $pool, UrlGeneratorInterface $urlGenerator)
    {
        $this->pool = $pool;
        $this->urlGenerator = $urlGenerator;
    }

    /**
     * {@inheritdoc}
     */
    public function enhance(Description $description)
    {
        $object = $description->getResource()->getPayload();

        // sonata has dependency on ClassUtils so this is fine.
        $class = ClassUtils::getClass($object);
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

        if (isset($links['list'])) {
            $description->set('list', $links['list']);
            unset($links['list']);
        }

        if (isset($links['create'])) {
            $description->set(Descriptor::LINK_CREATE_HTML, $links['create']);
            unset($links['create']);
        }

        if (isset($links['edit'])) {
            $description->set(Descriptor::LINK_EDIT_HTML, $links['edit']);
            unset($links['edit']);
        }

        if (isset($links['delete'])) {
            $description->set(Descriptor::LINK_REMOVE_HTML, $links['delete']);
            unset($links['delete']);
        }

        if (isset($links['show'])) {
            $description->set(Descriptor::LINK_SHOW_HTML, $links['show']);
            unset($links['show']);
        }

        $description->set(Descriptor::PAYLOAD_TITLE, $admin->toString($object));
        $description->set(Descriptor::TYPE_ALIAS, $admin->getLabel());
    }

    /**
     * {@inheritdoc}
     */
    public function supports(PuliResource $resource)
    {
        if (false === $resource instanceof CmfResource) {
            return false;
        }

        $payload = $resource->getPayload();

        // sonata has dependency on ClassUtils so this is fine.
        $class = ClassUtils::getClass($payload);

        return $this->pool->hasAdminByClass($class);
    }
}
