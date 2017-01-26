<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2017 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Component\Resource\Description\Enhancer\Sylius;

use Sylius\Bundle\ResourceBundle\Controller\RequestConfigurationFactory;
use Sylius\Component\Resource\Metadata\Metadata;
use Sylius\Component\Resource\Metadata\RegistryInterface;
use Symfony\Cmf\Component\Resource\Description\Description;
use Symfony\Cmf\Component\Resource\Description\DescriptionEnhancerInterface;
use Symfony\Cmf\Component\Resource\Description\Descriptor;
use Symfony\Cmf\Component\Resource\Puli\Api\PuliResource;
use Symfony\Cmf\Component\Resource\Repository\Resource\CmfResource;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Add descriptors from the Sylius Resource component.
 *
 * @author Daniel Leech <daniel@dantleech.com>
 *
 * @internal
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

    /**
     * @var RequestConfigurationFactory
     */
    private $requestConfigurationFactory;

    /**
     * @var RequestStack
     */
    private $requestStack;

    public function __construct(
        RegistryInterface $registry,
        RequestStack $requestStack,
        RequestConfigurationFactory $requestConfigurationFactory,
        UrlGeneratorInterface $urlGenerator
    ) {
        $this->registry = $registry;
        $this->urlGenerator = $urlGenerator;
        $this->requestConfigurationFactory = $requestConfigurationFactory;
        $this->requestStack = $requestStack;
    }

    /**
     * {@inheritdoc}
     */
    public function enhance(Description $description)
    {
        $metadata = $this->registry->getByClass($description->getResource()->getPayloadType());
        $payload = $description->getResource()->getPayload();

        // the request configuration provides the route names.
        $request = $this->requestStack->getCurrentRequest();
        $configuration = $this->requestConfigurationFactory->create($metadata, $request);

        $map = [
            Descriptor::LINK_SHOW_HTML => 'show',
            Descriptor::LINK_LIST_HTML => 'index',
            Descriptor::LINK_EDIT_HTML => 'update',
            Descriptor::LINK_CREATE_HTML => 'create',
            Descriptor::LINK_REMOVE_HTML => 'delete',
        ];

        foreach ($map as $descriptor => $action) {
            // note that some resources may not have routes
            // registered with sonata (f.e. folder resources)
            // so we ignore route-not-found exceptions.
            try {
                $url = $this->urlGenerator->generate(
                    $configuration->getRouteName($action),
                    [
                        'id' => $payload->getId(),
                    ]
                );
                $description->set($descriptor, $url);
            } catch (RouteNotFoundException $e) {
            }
        }

        // if a previous enhancer has set the children types descriptor, then
        // we can generate the LINKS_CREATE_CHILD_HTML descriptor.
        if ($description->has(Descriptor::CHILDREN_TYPES)) {
            $this->processChildrenTypes($description, $metadata, $request, $payload);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function supports(PuliResource $resource)
    {
        if (false === $resource instanceof CmfResource) {
            return false;
        }

        try {
            $this->registry->getByClass($resource->getPayloadType());
        } catch (\InvalidArgumentException $e) {
            return false;
        }

        return true;
    }

    private function processChildrenTypes(Description $description, Metadata $metadata, Request $request, $payload)
    {
        $childClasses = $description->get(Descriptor::CHILDREN_TYPES);
        $childLinks = [];

        foreach ($childClasses as $childClass) {
            try {
                $metadata = $this->registry->getByClass($childClass);
            } catch (\InvalidArgumentException $e) {
                continue;
            }

            $configuration = $this->requestConfigurationFactory->create($metadata, $request);

            $url = $this->urlGenerator->generate(
                $configuration->getRouteName('create')
            );

            $childLinks[$metadata->getAlias()] = $url.'?parent='.$payload->getId();
        }

        $description->set(Descriptor::LINKS_CREATE_CHILD_HTML, $childLinks);
    }
}
