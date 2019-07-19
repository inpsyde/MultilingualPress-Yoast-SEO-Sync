<?php # -*- coding: utf-8 -*-
/*
 * This file is part of the MultilingualPress Extensions Boilerplate package.
 *
 * (c) Inpsyde GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Inpsyde\MultilingualPress\YoastSeoSync\TranslationUi\Term;

use Inpsyde\MultilingualPress\TranslationUi\Term\MetaboxField;
use Inpsyde\MultilingualPress\TranslationUi\Term\MetaboxTab;
use Inpsyde\MultilingualPress\YoastSeoSync\TranslationUi\Term\Field\CanonicalUrl;
use Inpsyde\MultilingualPress\YoastSeoSync\TranslationUi\Term\Field\FocusKeyphrase;
use Inpsyde\MultilingualPress\YoastSeoSync\TranslationUi\Term\Field\MetaDescription;
use Inpsyde\MultilingualPress\YoastSeoSync\TranslationUi\Term\Field\Title;

class MetaboxFields
{
    const TAB = 'tab-yoast';
    const FIELD_FOCUS_KEYPHRASE = 'yoast_wpseo_focuskw';
    const FIELD_TITLE = 'yoast_wpseo_title';
    const FIELD_META_DESCRIPTION = 'yoast_wpseo_metadesc';
    const FIELD_CANONICAL = 'yoast_wpseo_canonical';

    /**
     * Creates fields for Yoast tab.
     * @return array
     */
    public function allFieldsTabs(): array
    {
        return [
            new MetaboxTab(
                self::TAB,
                esc_html_x(
                    'Yoast SEO',
                    'translation term metabox',
                    'multilingualpress-yoast-seo-sync'
                ),
                new MetaboxField(
                    self::FIELD_FOCUS_KEYPHRASE,
                    new FocusKeyphrase(),
                    [FocusKeyphrase::class, 'sanitize']
                ),
                new MetaboxField(
                    self::FIELD_TITLE,
                    new Title(),
                    [Title::class, 'sanitize']
                ),
                new MetaboxField(
                    self::FIELD_META_DESCRIPTION,
                    new MetaDescription(),
                    [MetaDescription::class, 'sanitize']
                ),
                new MetaboxField(
                    self::FIELD_CANONICAL,
                    new CanonicalUrl(),
                    [CanonicalUrl::class, 'sanitize']
                )
            ),
        ];
    }
}
