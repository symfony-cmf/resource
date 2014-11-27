<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2014 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Component\Resource;

use Puli\Resource\ResourceInterface;
use PHPCR\Util\PathHelper;

/**
 * Resource for objects, intended for use with content repositories
 * documents but could be used in other contexts (i.e. Doctrine entities).
 *
 * @author Daniel Leech <daniel@dantleech.com>
 */
class ObjectResource implements ResourceInterface
{
    /**
     * @var string
     */
    private $path;

    /**
     * @var object
     */
    private $object;

    /**
     * @param string $path Absolute path to object (e.g. /cmf/foobar/mynode)
     * @param object $object
     */
    public function __construct($path, $object)
    {
        if (!is_object($object)) {
            throw new \InvalidArgumentException(sprintf(
                'The ObjectResource should be passed an object, was passed an "%s"',
                gettype($object)
            ));
        }

        $this->path = $path;
        $this->object = $object;
    }

    /**
     * Return the object
     *
     * @return object
     */
    public function getObject()
    {
        return $this->object;
    }

    /**
     * Return the "directory" path for the object
     *
     * @return string
     */
    public function getPath()
    {
        return PathHelper::getParentPath($this->path);
    }

    /**
     * Return the name of the object
     *
     * @return string
     */
    public function getName()
    {
        return PathHelper::getNodeName($this->path);
    }
}
