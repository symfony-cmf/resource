<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2017 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Component\Resource\Repository\Resource;

use Doctrine\Common\Util\ClassUtils;
use PHPCR\DocumentInterface;
use PHPCR\Util\PathHelper;
use Puli\Repository\Api\Resource\Resource;

/**
 * Resource representing a PHPCR document.
 *
 * @author Daniel Leech <daniel@dantleech.com>
 *
 * @internal
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
     * {@inheritdoc}
     */
    public function getPayloadType()
    {
        return ClassUtils::getRealClass(get_class($this->document));
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return PathHelper::getNodeName($this->getPath());
    }
}
