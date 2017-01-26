<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2017 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Component\Resource\Repository\Resource\Metadata;

use PHPCR\NodeInterface;
use Symfony\Cmf\Component\Resource\Puli\Api\ResourceMetadata;

/**
 * Metadata for PHPCR node.
 *
 * @author Daniel Leech <daniel@dantleech.com>
 *
 * @internal
 */
class PhpcrMetadata extends ResourceMetadata
{
    private $node;

    /**
     * @param NodeInterface $node
     */
    public function __construct(NodeInterface $node)
    {
        $this->node = $node;
    }

    /**
     * {@inheritdoc}
     */
    public function getCreationTime()
    {
        if (!$this->node->isNodeType('mix:created')) {
            return 0;
        }

        $date = $this->node->getProperty('jcr:created')->getDate();

        return $date->format('U');
    }

    /**
     * {@inheritdoc}
     */
    public function getModificationTime()
    {
        if (!$this->node->isNodeType('mix:lastModified')) {
            return 0;
        }

        $date = $this->node->getProperty('jcr:lastModified')->getDate();

        return $date->format('U');
    }
}
