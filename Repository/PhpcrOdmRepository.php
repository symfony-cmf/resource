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
use Doctrine\Common\Persistence\ManagerRegistry;
use Puli\Repository\ResourceNotFoundException;
use Puli\Resource\Collection\ResourceCollection;
use Symfony\Cmf\Component\Resource\ObjectResource;

class PhpcrOdmRepository implements ResourceRepositoryInterface
{
    /**
     * @var ManagerRegistry
     */
    private $managerRegistry;

    /**
     * @var FinderInterface
     */
    private $finder;

    public function __construct(ManagerRegistry $managerRegistry, FinderInterface $finder)
    {
        $this->managerRegistry = $managerRegistry;
        $this->finder = $finder;
    }

    protected function getManager()
    {
        return $this->managerRegistry->getManager();
    }

    /**
     * {@inheritDoc}
     */
    public function get($path)
    {
        $document = $this->getManager()->find(null, $path);

        if (null === $document) {
            throw new ResourceNotFoundException(sprintf(
                'No PHPCR-ODM document could be found at "%s"',
                $path
            ));
        }

        $absPath = $this->getManager()->getNodeForDocument($document)->getPath();
        $resource = new ObjectResource($absPath, $document);

        return $resource;
    }

    /**
     * We could support this by implenting some glob utility which could
     * also be used in PHPCR-Shell or by using XPath queries.
     *
     * {@inheritDoc}
     */
    public function find($selector)
    {
        $phpcrNodes = $this->finder->find($selector);

        foreach ($phpcrNodes as $phpcrNode) {
            $paths = $phpcrNode->getPath();
        }

        return $this->getManager()->findMany(null, $paths);
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
