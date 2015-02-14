<?php

namespace Symfony\Cmf\Component\Resource\Repository\Resource;

use Puli\Repository\Api\Resource\Resource;
use PHPCR\DocumentInterface;
use PHPCR\Util\PathHelper;
use Puli\Repository\Resource\GenericResource;
use Doctrine\Common\Util\ClassUtils;

/**
 * Resource representing a PHPCR document
 *
 * @author Daniel Leech <daniel@dantleech.com>
 */
class PhpcrOdmResource extends CmfResource
{
    private $document;

    /**
     * @param string            $path
     * @param DocumentInterface $document
     */
    public function __construct($path, $document)
    {
        parent::__construct($path);
        $this->document = $document;
    }

    /**
     * Return the PHPCR ODM document which this resource
     * represents.
     *
     * @return DocumentInterface
     */
    public function getPayload()
    {
        return $this->document;
    }

    /**
     * {@inheritDoc}
     */
    public function getPayloadType()
    {
        return ClassUtils::getRealClass(get_class($this->document));
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return PathHelper::getNodeName($this->getPath());
    }
}
