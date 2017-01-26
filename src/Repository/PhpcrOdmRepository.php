<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2017 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Component\Resource\Repository;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ODM\PHPCR\DocumentManagerInterface;
use DTL\Glob\Finder\PhpcrOdmTraversalFinder;
use DTL\Glob\FinderInterface;
use Symfony\Cmf\Component\Resource\Puli\ArrayResourceCollection;
use Symfony\Cmf\Component\Resource\Repository\Resource\PhpcrOdmResource;

/**
 * @internal
 */
class PhpcrOdmRepository extends AbstractPhpcrRepository
{
    /**
     * @var ManagerRegistry
     */
    private $managerRegistry;

    public function __construct(ManagerRegistry $managerRegistry, $basePath = null, FinderInterface $finder = null)
    {
        $finder = $finder ?: new PhpcrOdmTraversalFinder($managerRegistry);
        parent::__construct($finder, $basePath);
        $this->managerRegistry = $managerRegistry;
    }

    /**
     * @return DocumentManagerInterface
     */
    protected function getManager()
    {
        return $this->managerRegistry->getManager();
    }

    /**
     * {@inheritdoc}
     */
    public function get($path)
    {
        $document = $this->getDocument($path);
        $resource = new PhpcrOdmResource($path, $document);
        $resource->attachTo($this);

        return $resource;
    }

    /**
     * {@inheritdoc}
     */
    public function listChildren($path)
    {
        $resource = $this->get($path);
        $children = $this->getManager()->getChildren($resource->getPayload());

        return $this->buildCollection($children->toArray());
    }

    /**
     * {@inheritdoc}
     */
    public function contains($selector, $language = 'glob')
    {
        return count($this->find($selector, $language)) > 0;
    }

    /**
     * {@inheritdoc}
     */
    public function findByTag($tag)
    {
        throw new \Exception('Find by tag not supported');
    }

    /**
     * {@inheritdoc}
     */
    public function getTags()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function reorderNode($sourcePath, $position)
    {
        $document = $this->getDocument($sourcePath);
        $node = $this->getManager()->getNodeForDocument($document);
        $parent = $node->getParent();
        $nodeNames = iterator_to_array($parent->getNodeNames());
        $parentDocument = $this->getManager()->find(null, $parent->getPath());

        if ($position == 0) {
            $this->getManager()->reorder($parentDocument, $node->getName(), $nodeNames[$position], true);
        } elseif (isset($nodeNames[$position + 1])) {
            $this->getManager()->reorder($parentDocument, $node->getName(), $nodeNames[$position + 1], true);
        } else {
            $lastName = end($nodeNames);
            $this->getManager()->reorder($parentDocument, $node->getName(), $lastName, false);
        }

        $this->getManager()->flush();
    }

    /**
     * {@inheritdoc}
     */
    protected function buildCollection(array $documents)
    {
        $collection = new ArrayResourceCollection();

        if (empty($documents)) {
            return $collection;
        }

        $uow = $this->getManager()->getUnitOfWork();

        foreach ($documents as $document) {
            $childPath = $uow->getDocumentId($document);
            $path = $this->unresolvePath($childPath);
            $resource = new PhpcrOdmResource($path, $document);
            $resource->attachTo($this);
            $collection->add($resource);
        }

        return $collection;
    }

    /**
     * {@inheritdoc}
     */
    protected function removeNodes(array $documents)
    {
        foreach ($documents as $document) {
            $this->getManager()->remove($document);
        }

        $this->getManager()->flush();
    }

    /**
     * {@inheritdoc}
     */
    protected function moveNodes(array $documents, $sourceQuery, $targetPath)
    {
        $this->doMoveNodes($documents, $sourceQuery, $targetPath);
        $this->getManager()->flush();
    }

    private function doMoveNodes(array $documents, $sourceQuery, $targetPath)
    {
        if (false === $this->isGlobbed($sourceQuery)) {
            return $this->getManager()->move(current($documents), $targetPath);
        }

        foreach ($documents as $document) {
            $node = $this->getManager()->getNodeForDocument($document);
            $this->getManager()->move($document, $targetPath.'/'.$node->getName());
        }
    }

    private function getDocument($path)
    {
        $resolvedPath = $this->resolvePath($path);
        $document = $this->getManager()->find(null, $resolvedPath);

        if (null === $document) {
            throw new \RuntimeException(sprintf(
                'No PHPCR-ODM document could be found at "%s"',
                $resolvedPath
            ));
        }

        return $document;
    }
}
