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

use Puli\Repository\ResourceNotFoundException;
use Puli\Resource\Collection\ResourceCollection;
use Symfony\Cmf\Component\Resource\ObjectResource;
use Symfony\Cmf\Component\Resource\FinderInterface;
use PHPCR\SessionInterface;
use DTL\Glob\Finder\PhpcrTraversalFinder;
use DTL\Glob\FinderInterface;

/**
 * Resource repository for PHPCR
 *
 * @author Daniel Leech <daniel@dantleech.com>
 */
class PhpcrRepository extends AbstractPhpcrRepository
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
     * @param SessionInterface $session
     * @param FinderInterface $finder
     * @param string $basePath
     */
    public function __construct(SessionInterface $session, $basePath = null, FinderInterface $finder = null)
    {
        parent::__construct($basePath);
        $this->session = $session;
        $this->finder = $finder ? : new PhpcrTraversalFinder($session);
    }

    /**
     * {@inheritDoc}
     */
    public function get($path)
    {
        try {
            $node = $this->session->getNode($this->getPath($path));
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
    public function find($selector, $language = 'glob')
    {
        if ($language != 'glob') {
            throw new UnsupportedLanguageException($language);
        }

        $nodes = $this->finder->find($selector);

        return $this->buildCollection($nodes);
    }

    public function listChildren($path)
    {
        $node = $this->get($path);

        return $this->buildCollection($node->getNodes());
    }

    /**
     * {@inheritDoc}
     */
    public function contains($selector, $language = 'glob')
    {
        return count($this->find($selector, $language)) > 0;
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

    protected function createResource($path, $object)
    {
        return new PhpcrResource($path, $object);
    }
}
