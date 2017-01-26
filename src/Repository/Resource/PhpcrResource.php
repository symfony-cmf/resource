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

use PHPCR\NodeInterface;
use Puli\Repository\Api\Resource\Resource;
use Symfony\Cmf\Component\Resource\Repository\Resource\Metadata\PhpcrMetadata;

/**
 * Resource representing a PHPCR node.
 *
 * @author Daniel Leech <daniel@dantleech.com>
 *
 * @internal
 */
class PhpcrResource extends CmfResource
{
    private $node;

    /**
     * @param string        $path
     * @param NodeInterface $node
     */
    public function __construct($path, NodeInterface $node)
    {
        parent::__construct($path);
        $this->node = $node;
    }

    /**
     * {@inheritdoc}
     */
    public function getPayload()
    {
        return $this->node;
    }

    /**
     * {@inheritdoc}
     */
    public function getPayloadType()
    {
        return $this->node->getPrimaryNodeType()->getName();
    }

    /**
     * {@inheritdoc}
     */
    public function getMetadata()
    {
        return new PhpcrMetadata($this->node);
    }
}
