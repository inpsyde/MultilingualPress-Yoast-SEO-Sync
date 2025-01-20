<?php # -*- coding: utf-8 -*-

declare(strict_types=1);

namespace Inpsyde\MultilingualPress\YoastSeoSync\TranslationUi\Term\Field;

use Inpsyde\MultilingualPress\TranslationUi\MetaboxFieldsHelper;
use Inpsyde\MultilingualPress\TranslationUi\Term\RelationshipContext;
use Inpsyde\MultilingualPress\YoastSeoSync\TranslationUi\Term\MetaboxFields;
use Inpsyde\MultilingualPress\YoastSeoSync\TranslationUi\Term\Repository;

class CanonicalUrl
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
                       value="<?= esc_url($value) ?>"/>
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
        try {
            return $this->repository->optionByContext($relationshipContext, 'wpseo_canonical');
        } catch (\DomainException $exception) {
            return '';
        }
    }
}
