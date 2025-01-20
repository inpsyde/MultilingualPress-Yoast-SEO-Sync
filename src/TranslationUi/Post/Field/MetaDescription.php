<?php # -*- coding: utf-8 -*-

declare(strict_types=1);

namespace Inpsyde\MultilingualPress\YoastSeoSync\TranslationUi\Post\Field;

use Inpsyde\MultilingualPress\TranslationUi\MetaboxFieldsHelper;
use Inpsyde\MultilingualPress\TranslationUi\Post\RelationshipContext;
use Inpsyde\MultilingualPress\YoastSeoSync\TranslationUi\Post\MetaboxFields;

class MetaDescription
{
    /**
     * @param MetaboxFieldsHelper $helper
     * @param RelationshipContext $context
     */
    public function __invoke(MetaboxFieldsHelper $helper, RelationshipContext $context)
    {
        $id = $helper->fieldId(MetaboxFields::FIELD_META_DESCRIPTION);
        $name = $helper->fieldName(MetaboxFields::FIELD_META_DESCRIPTION);
        $value = $this->value($context);
        ?>
        <tr>
            <th scope="row">
                <label>
                    <?php esc_html_e('Meta description', 'multilingualpress-yoast-seo-sync') ?>
                </label>
            </th>
            <td>
                <textarea
                    name="<?= esc_attr($name) ?>"
                    id="<?= esc_attr($id) ?>"
                    rows="3"
                    class="large-text"><?= wp_kses_post($value) ?></textarea>
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
        return sanitize_textarea_field($value);
    }

    /**
     * Retrieve the value for the input field.
     *
     * @param RelationshipContext $relationshipContext
     * @return string
     */
    private function value(RelationshipContext $relationshipContext): string
    {
        return (string)get_post_meta(
            $relationshipContext->remotePostId(),
            '_' . MetaboxFields::FIELD_META_DESCRIPTION,
            true
        );
    }
}
