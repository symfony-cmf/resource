<?php

namespace Symfony\Cmf\Component\Resource;

interface RepositoryFactoryInterface
{
    /**
     * Return a new instance of the named repository
     *
     * @param string $name
     *
     * @return Puli\Resource\RepositoryInterface
     */
    public function create($selector);
}

