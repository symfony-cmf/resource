<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2015 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Component\Resource\Metadata;

class PayloadMetadata
{
    private $metadata = [];
    private $supported = [];

    // shorthand reference for resource, e.g. app.page
    const TYPE_ALIAS = 'alias';

    // title for the resource type, e.g. "Pages"
    const TYPE_TITLE = 'title.type';

    // title to use for the resource
    const TITLE = 'title.type';

    // HTML links
    const LINK_EDIT_HTML = 'link.edit.html';
    const LINK_CREATE_HTML = 'link.create.html';
    const LINK_UPDATE_HTML = 'link.update.html';
    const LINK_REMOVE_HTML = 'link.remove.html';
    const LINK_SHOW_HTML = 'link.show.html';

    // REST links
    const LINK_EDIT_REST = 'link.edit.rest';
    const LINK_CREATE_REST = 'link.create.rest';
    const LINK_UPDATE_REST = 'link.update.rest';
    const LINK_REMOVE_REST = 'link.remove.rest';
    const LINK_SHOW_REST = 'link.show.rest';

    public function __construct($providerName, array $metadata)
    {
        $this->providerName = $providerName;
        $this->metadata = $metadata;
    }

    /**
     * Return the metadata value for the given key.
     *
     * The key should be one of the constants defined in this class.
     *
     * @param string $key
     * @return mixed
     */
    public function get($key)
    {
        if (!isset($this->supported[$key])) {
            throw new \InvalidArgumentException(sprintf(
                'Metadata key "%s" from "%s" not supported', 
                $key,
                $this->providerName
            ));
        }

        return $this->metadata[$key];
    }
}
