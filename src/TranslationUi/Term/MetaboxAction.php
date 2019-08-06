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

        $remoteSiteId = $this->relationshipContext->remoteSiteId();
        $remoteTermId = $this->relationshipContext->remoteTermId();

        $focuskw = $values["site-{$remoteSiteId}"][MetaboxFields::FIELD_FOCUS_KEYPHRASE];
        $title = $values["site-{$remoteSiteId}"][Metaboxfields::FIELD_TITLE];
        $metadesc = $values["site-{$remoteSiteId}"][Metaboxfields::FIELD_META_DESCRIPTION];
        $canonical = $values["site-{$remoteSiteId}"][MetaboxFields::FIELD_CANONICAL];

        $option = get_blog_option($remoteSiteId, 'wpseo_taxonomy_meta');
        $term = get_term($remoteTermId);
        $taxonomy = $term->taxonomy;

        $option[$taxonomy][$remoteTermId][MetaboxFields::FIELD_FOCUS_KEYPHRASE] = $focuskw;
        $option[$taxonomy][$remoteTermId][Metaboxfields::FIELD_TITLE] = $title;
        $option[$taxonomy][$remoteTermId][Metaboxfields::FIELD_META_DESCRIPTION] = $metadesc;
        $option[$taxonomy][$remoteTermId][MetaboxFields::FIELD_CANONICAL] = $canonical;

        return update_blog_option($remoteSiteId, 'wpseo_taxonomy_meta', $option);
    }
}
