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

namespace Inpsyde\MultilingualPress\YoastSeoSync\TranslationUi\Post\Field;

use Inpsyde\MultilingualPress\TranslationUi\MetaboxFieldsHelper;
use Inpsyde\MultilingualPress\TranslationUi\Post\RelationshipContext;
use Inpsyde\MultilingualPress\YoastSeoSync\TranslationUi\Post\MetaboxFields;

class CanonicalUrl
{
    /**
     * @param MetaboxFieldsHelper $helper
     * @param RelationshipContext $context
     */
    public function __invoke(MetaboxFieldsHelper $helper, RelationshipContext $context)
    {
        $id = $helper->fieldId(MetaboxFields::FIELD_CANONICAL);
        $name = $helper->fieldName(MetaboxFields::FIELD_CANONICAL);
        $value = $this->value($context);

        ?>
        <tr>
            <th scope="row">
                <label>
                    <?php esc_html_e('Canonical URL', 'multilingualpress-yoast-seo-sync') ?>
                </label>
            </th>
            <td>
                <input type="url"
                       class="large-text"
                       name="<?= esc_attr($name) ?>"
                       id="<?= esc_attr($id) ?>"
                       value="<?= esc_attr($value) ?>"/>
            </td>
        </tr>
        <?php
    }

    /**
     * @param string $value
     * @return string
     */
    public static function sanitize(string $value): string
    {
        return esc_url_raw($value);
    }

    /**
     * Retrieve the value for the input field.
     *
     * @param RelationshipContext $relationshipContext
     * @return string
     */
    private function value(RelationshipContext $relationshipContext): string
    {
        return (string) get_post_meta(
            $relationshipContext->remotePostId(),
            '_yoast_wpseo_canonical',
            true
        );
    }
}
