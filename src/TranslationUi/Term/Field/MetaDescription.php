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

namespace Inpsyde\MultilingualPress\YoastSeoSync\TranslationUi\Term\Field;

use Inpsyde\MultilingualPress\TranslationUi\MetaboxFieldsHelper;
use Inpsyde\MultilingualPress\TranslationUi\Term\RelationshipContext;
use Inpsyde\MultilingualPress\YoastSeoSync\TranslationUi\Term\MetaboxFields;
use Inpsyde\MultilingualPress\YoastSeoSync\TranslationUi\Term\Repository;

class MetaDescription
{
    /**
     * @var Repository
     */
    private $repository;

    /**
     * @param Repository $repository
     */
    public function __construct(Repository $repository)
    {
        $this->repository = $repository;
    }

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
        return $this->repository->optionByContext($relationshipContext, MetaboxFields::FIELD_META_DESCRIPTION);
    }
}
