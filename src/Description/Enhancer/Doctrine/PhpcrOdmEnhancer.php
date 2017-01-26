<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2017 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Component\Resource\Description\Enhancer\Doctrine;

use Doctrine\Common\Util\ClassUtils;
use Doctrine\ODM\PHPCR\Mapping\ClassMetadataFactory;
use Symfony\Cmf\Component\Resource\Description\Description;
use Symfony\Cmf\Component\Resource\Description\DescriptionEnhancerInterface;
use Symfony\Cmf\Component\Resource\Description\Descriptor;
use Symfony\Cmf\Component\Resource\Puli\Api\PuliResource;
use Symfony\Cmf\Component\Resource\Repository\Resource\CmfResource;

/**
 * Add descriptors from the Doctrine PHPCR ODM.
 *
 * @author Daniel Leech <daniel@dantleech.com>
 *
 * @internal
 */
class PhpcrOdmEnhancer implements DescriptionEnhancerInterface
{
    private $metadataFactory;

    public function __construct(ClassMetadataFactory $metadataFactory)
    {
        $this->metadataFactory = $metadataFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function enhance(Description $description)
    {
        $metadata = $this->metadataFactory->getMetadataFor($description->getResource()->getPayloadType());
        $childClasses = $metadata->getChildClasses();
        $childTypes = [];

        // explode the allowed types into concrete classes
        foreach ($this->metadataFactory->getAllMetadata() as $childMetadata) {
            foreach ($childClasses as $childClass) {
                if ($childClass == $childMetadata->name || $childMetadata->getReflectionClass()->isSubclassOf($childClass)) {
                    $childTypes[] = $childMetadata->name;
                }
            }
        }

        $description->set(Descriptor::CHILDREN_ALLOW, !$metadata->isLeaf());
        $description->set(Descriptor::CHILDREN_TYPES, $childTypes);
    }

    /**
     * {@inheritdoc}
     */
    public function supports(PuliResource $resource)
    {
        if (false === $resource instanceof CmfResource) {
            return false;
        }

        return $this->metadataFactory->hasMetadataFor(ClassUtils::getRealClass($resource->getPayloadType()));
    }
}
