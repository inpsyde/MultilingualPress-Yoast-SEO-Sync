<?php # -*- coding: utf-8 -*-
declare(strict_types=1);

namespace Inpsyde\MultilingualPress\YoastSeoSync\TranslationUi\Term;

use Inpsyde\MultilingualPress\Framework\Admin\Metabox\Action;
use Inpsyde\MultilingualPress\Framework\Admin\PersistentAdminNotices;
use Inpsyde\MultilingualPress\Framework\Http\Request;
use Inpsyde\MultilingualPress\TranslationUi\MetaboxFieldsHelper;
use Inpsyde\MultilingualPress\TranslationUi\Term;

final class MetaboxAction implements Action
{
    /**
     * @var MetaboxFields
     */
    private $metaboxFields;

    /**
     * @var MetaboxFieldsHelper
     */
    private $fieldsHelper;

    /**
     * @var Term\RelationshipContext
     */
    private $relationshipContext;

    public function __construct(
        MetaboxFields $metaboxFields,
        MetaboxFieldsHelper $fieldsHelper,
        Term\RelationshipContext $relationshipContext
    ) {

        $this->metaboxFields = $metaboxFields;
        $this->fieldsHelper = $fieldsHelper;
        $this->relationshipContext = $relationshipContext;
    }

    public function save(Request $request, PersistentAdminNotices $notices): bool
    {
        $values = $request->bodyValue(
            'multilingualpress',
            INPUT_POST,
            FILTER_UNSAFE_RAW,
            FILTER_FORCE_ARRAY
        );
        $title = $values["site-{$this->relationshipContext->remoteSiteId()}"]["yoast_wpseo_title"];

        $option = get_blog_option($this->relationshipContext->remoteSiteId(), 'wpseo_taxonomy_meta');
        $term = get_term($this->relationshipContext->remoteTermId());
        $taxonomy = $term->taxonomy;
        $option[$taxonomy][$this->relationshipContext->remoteTermId()]['wpseo_title'] = $title;

        return update_blog_option($this->relationshipContext->remoteSiteId(), 'wpseo_taxonomy_meta', $option);
    }
}
