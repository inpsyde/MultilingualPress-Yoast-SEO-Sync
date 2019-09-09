<?php # -*- coding: utf-8 -*-
/*
 * This file is part of the MultilingualPress Yoast Seo Sync package.
 *
 * (c) Inpsyde GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Inpsyde\MultilingualPress\YoastSeoSync\TranslationUi\Term;

use Inpsyde\MultilingualPress\TranslationUi\Term\RelationshipContext;

class Repository
{
    const OPTION_KEY = 'wpseo_taxonomy_meta';

    /**
     * @param RelationshipContext $relationshipContext
     * @param string $optionKey
     * @return string
     * @throws \DomainException
     */
    public function optionByContext(RelationshipContext $relationshipContext, string $optionKey): string
    {
        if(!$optionKey) {
            throw new \InvalidArgumentException('Option key cannot be empty.');
        }

        $option = (array)(get_blog_option($relationshipContext->remoteSiteId(), self::OPTION_KEY, []) ?: []);
        if (!$option) {
            return '';
        }

        $term = get_term($relationshipContext->remoteTermId());
        if($term instanceof \WP_Error) {
            /** @var \WP_Error $term */
            throw new \DomainException($term->get_error_message());
        }

        return $option[$term->taxonomy][$term->term_id][$optionKey] ?? '';
    }
}
