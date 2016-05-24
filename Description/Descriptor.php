<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2015 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Component\Resource\Description;

/**
 * Class containing standard description keys which should be used when appropriate
 * by enrichers in order to provide a level of interoperability.
 */
final class Descriptor
{
    /**
     * Alias for the resource type for example `app.page`.
     */
    const TYPE_ALIAS = 'type.alias';

    /**
     * Humanized representation of the resource type.
     */
    const TYPE_TITLE = 'type.title';

    /**
     * Title of the actual payload, e.g. "My Blog Post".
     */
    const PAYLOAD_TITLE = 'title';

    /**
     * Keys to be used to store link values for HTML.
     */
    const LINK_EDIT_HTML = 'link.edit.html';
    const LINK_CREATE_HTML = 'link.create.html';
    const LINK_UPDATE_HTML = 'link.update.html';
    const LINK_REMOVE_HTML = 'link.remove.html';
    const LINK_SHOW_HTML = 'link.show.html';

    /**
     * Keys to be used to store link values for REST.
     */
    const LINK_EDIT_REST = 'link.edit.rest';
    const LINK_CREATE_REST = 'link.create.rest';
    const LINK_UPDATE_REST = 'link.update.rest';
    const LINK_REMOVE_REST = 'link.remove.rest';
    const LINK_SHOW_REST = 'link.show.rest';
}
