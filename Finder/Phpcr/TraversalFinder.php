<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2014 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Component\Resource\Finder\Phpcr;

use Puli\Resource\ResourceInterface;
use Symfony\Cmf\Component\Resource\FinderInterface;
use Puli\Resource\Collection\ResourceCollection;
use Symfony\Cmf\Component\Resource\Finder\SelectorParser;
use PHPCR\NodeInterface;
use PHPCR\SessionInterface;

/**
 * PHPCR finder which users traversal.
 *
 * Supports single-star matching on path elements.
 * Currently does not support the double-star syntax
 * for "deep" recursing.
 *
 * @author Daniel Leech <daniel@dantleech.com>
 */
class TraversalFinder implements FinderInterface
{
    /**
     * @var SelectorParser
     */
    private $parser;

    /**
     * @var SessionInterface
     */
    private $session;

    /**
     * @param SessionInterface $session
     * @param SelectorParser $parser
     */
    public function __construct(SessionInterface $session, SelectorParser $parser)
    {
        $this->parser = $parser;
        $this->session = $session;
    }

    /**
     * {@inheritDoc}
     */
    public function find($selector)
    {
        if ($selector == '/') {
            return array($this->getNode(array()));
        }

        $segments = $this->parser->parse($selector);

        $result = array();
        $this->traverse(null, $segments, $result);

        return $result;
    }

    /**
     * Traverse the node
     *
     * @param NodeInterface|null $node  The node to traverse, if it exists yet
     * @param array $segments  The element => token stack
     * @param array $result  The result
     *
     * @return null
     */
    private function traverse($node = null, $segments, &$result = array())
    {
        $path = array();

        if (null !== $node) {
            $path = explode('/', substr($node->getPath(), 1));
        }

        do {
            list($element, $bitmask) = array_shift($segments);

            if ($bitmask & SelectorParser::T_STATIC) {
                $path[] = $element;

                if ($bitmask & SelectorParser::T_LAST) {
                    if ($node = $this->getNode($path)) {
                        $result[] = $node;
                        break;
                    }
                }
            }

            if ($bitmask & SelectorParser::T_PATTERN) {
                if (null === $parentNode = $this->getNode($path)) {
                    return;
                }

                $children = $parentNode->getNodes($element);

                foreach ($children as $child) {
                    if ($bitmask & SelectorParser::T_LAST) {
                        $result[] = $child;
                    } else {
                        $this->traverse($child, $segments, $result);
                    }
                }

                return;
            }
        } while (count($segments));
    }

    private function getNode(array $path)
    {
        $absPath = '/' . implode('/', $path);

        try {
            $node = $this->session->getNode($absPath);
        } catch (\PHPCR\PathNotFoundException $e) {
            $node = null;
        }

        return $node;
    }
}
