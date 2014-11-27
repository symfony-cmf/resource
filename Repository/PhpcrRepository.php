<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2014 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Component\Resource\Repository;

use Puli\Repository\ResourceRepositoryInterface;
use Puli\Repository\ResourceNotFoundException;
use Puli\Resource\Collection\ResourceCollection;
use Symfony\Cmf\Component\Resource\ObjectResource;
use Symfony\Cmf\Component\Resource\FinderInterface;
use PHPCR\SessionInterface;

/**
 * Resource repository for PHPCR
 *
 * @author Daniel Leech <daniel@dantleech.com>
 */
class PhpcrRepository implements ResourceRepositoryInterface
{
    /**
     * @var ManagerRegistry
     */
    private $session;

    /**
     * @var FinderInterface
     */
    private $finder;

    /**
     * @param SessionInterface
     * @param FinderInterface
     */
    public function __construct(SessionInterface $session, FinderInterface $finder)
    {
        $this->session = $session;
        $this->finder = $finder;
    }

    /**
     * {@inheritDoc}
     */
    public function get($path)
    {
        try {
            $node = $this->session->getNode($path);
        } catch (\PathNotFoundException $e) {
            throw new ResourceNotFoundException(sprintf(
                'No PHPCR node could be found at "%s"',
                $path
            ), null, $e);
        }

        $resource = new ObjectResource($node->getPath(), $node);

        return $resource;
    }

    /**
     * {@inheritDoc}
     */
    public function find($selector)
    {
        $nodes = $this->finder->find($selector);
        $collection = new ResourceCollection();

        foreach ($nodes as $node) {
            $collection->add(new ObjectResource($node->getPath(), $node));
        }

        return $collection;
    }

    /**
     * {@inheritDoc}
     */
    public function contains($selector)
    {
        return count($this->find($selector)) > 0;
    }

    /**
     * {@inheritDoc}
     */
    public function findByTag($tag)
    {
        throw new \Exception('Get by tag not currently supported');
    }

    /**
     * {@inheritDoc}
     */
    public function getTags()
    {
        return array();
    }
}
