<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2017 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Component\Resource\Description;

/**
 * Class containing recommended descriptors for use by enhancers in order to
 * provide a level of interoperability.
 *
 * @internal
 */
final class Descriptor
{
    /**
     * Alias for the resource type for example `app.page`.
     * Value should be a scalar string.
     */
    const TYPE_ALIAS = 'type.alias';

    /**
     * Humanized representation of the resource type.
     * Value should be a scalar string.
     */
    const TYPE_TITLE = 'type.title';

    /**
     * Title of the actual payload, e.g. "My Blog Post".
     * Value should be a scalar string.
     */
    const PAYLOAD_TITLE = 'title';

    /**
     * Descriptors for HTML links.
     * Values should be either a valid URI.
     */
    const LINK_EDIT_HTML = 'link.edit.html';
    const LINK_CREATE_HTML = 'link.create.html';
    const LINK_UPDATE_HTML = 'link.update.html';
    const LINK_REMOVE_HTML = 'link.remove.html';
    const LINK_SHOW_HTML = 'link.show.html';
    const LINK_LIST_HTML = 'link.list.html';

    /**
     * Array of links for creating child resources.
     */
    const LINKS_CREATE_CHILD_HTML = 'links.create_child.html';

    /**
     * Descriptors for REST links.
     * Values should be either a valid URI.
     */
    const LINK_EDIT_REST = 'link.edit.rest';
    const LINK_CREATE_REST = 'link.create.rest';
    const LINK_UPDATE_REST = 'link.update.rest';
    const LINK_REMOVE_REST = 'link.remove.rest';
    const LINK_SHOW_REST = 'link.show.rest';
    const LINK_LIST_REST = 'link.show.rest';

    /**
     * Permitted children types for this resource.
     * Value should be a scalar array, e.g. [ `stdClass`, `FooClass` ].
     *
     * NOTE: This should be an explicit list of types and should not include
     *       interfaces are abstract class names.
     */
    const CHILDREN_TYPES = 'children.types';

    /**
     * If children are allowed to be added to this resource.
     * Value should be a boolean.
     */
    const CHILDREN_ALLOW = 'children.allow';
}
