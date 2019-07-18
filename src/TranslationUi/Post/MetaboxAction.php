<?php # -*- coding: utf-8 -*-
declare(strict_types=1);

namespace Inpsyde\MultilingualPress\YoastSeoSync\TranslationUi\Post;

use Inpsyde\MultilingualPress\Core\Entity\ActivePostTypes;
use Inpsyde\MultilingualPress\Framework\Admin\Metabox\Action;
use Inpsyde\MultilingualPress\Framework\Admin\PersistentAdminNotices;
use Inpsyde\MultilingualPress\Framework\Http\Request;
use Inpsyde\MultilingualPress\TranslationUi\MetaboxFieldsHelper;
use Inpsyde\MultilingualPress\TranslationUi\Post;

/**
 * Class MetaboxAction
 *
 * @package Inpsyde\MultilingualPress\YoastSeoSync\TranslationUi\Post
 */
final class MetaboxAction implements Action
{
    /**
     * @var array
     */
    private static $calledCount = [];

    /**
     * @var Post\RelationshipContext
     */
    private $postRelationshipContext;

    /**
     * @var MetaboxFields
     */
    private $fields;

    /**
     * @var MetaboxFieldsHelper
     */
    private $fieldsHelper;

    /**
     * @var ActivePostTypes
     */
    private $postTypes;

    /**
     * @var Post\SourcePostSaveContext
     */
    private $sourcePostContext;

    /**
     * MetaboxAction constructor
     * @param MetaboxFields $metaboxFields
     * @param MetaboxFieldsHelper $fieldsHelper
     * @param Post\RelationshipContext $postRelationshipContext
     * @param ActivePostTypes $postTypes
     */
    public function __construct(
        MetaboxFields $metaboxFields,
        MetaboxFieldsHelper $fieldsHelper,
        Post\RelationshipContext $postRelationshipContext,
        ActivePostTypes $postTypes
    ) {

        $this->fields = $metaboxFields;
        $this->fieldsHelper = $fieldsHelper;
        $this->postRelationshipContext = $postRelationshipContext;
        $this->postTypes = $postTypes;
    }

    /**
     * @param Request $request
     * @param PersistentAdminNotices $notices
     * @return bool
     */
    public function save(Request $request, PersistentAdminNotices $notices): bool
    {
        $remotePostId = $this->postRelationshipContext->remotePostId();
        $sourceSiteId = $this->postRelationshipContext->sourceSiteId();
        $remoteSiteId = $this->postRelationshipContext->remoteSiteId();

        if ($sourceSiteId === $remoteSiteId || !$this->postRelationshipContext->hasRemotePost()) {
            return false;
        }

        if (!$this->isValidSaveRequest($this->sourceContext($request))) {
            return false;
        }
        if (!current_user_can('edit_post', $this->postRelationshipContext->remotePostId())) {
            return false;
        }

        $values = $this->allFieldsValues($request);
        if (!$values) {
            return false;
        }

        $this->updatePostMeta($values, $remotePostId, $notices);

        return true;
    }

    /**
     * Retrieve the source context for current post type
     *
     * @param Request $request
     * @return Post\SourcePostSaveContext
     */
    private function sourceContext(Request $request): Post\SourcePostSaveContext
    {
        if ($this->sourcePostContext) {
            return $this->sourcePostContext;
        }

        switch_to_blog($this->postRelationshipContext->sourceSiteId());
        $this->sourcePostContext = new Post\SourcePostSaveContext(
            $this->postRelationshipContext->sourcePost(),
            $this->postTypes,
            $request
        );
        restore_current_blog();

        return $this->sourcePostContext;
    }

    /**
     * Check if the current request should be processed by save().
     *
     * @param Post\SourcePostSaveContext $context
     * @return bool
     */
    private function isValidSaveRequest(Post\SourcePostSaveContext $context): bool
    {
        $site = $this->postRelationshipContext->remoteSiteId();
        array_key_exists($site, self::$calledCount) or self::$calledCount[$site] = 0;

        // For auto-drafts, 'save_post' is called twice, resulting in doubled drafts for translations.
        self::$calledCount[$site]++;

        return
            $context->postType()
            && $context->postStatus()
            && ($context->postStatus() !== 'auto-draft' || self::$calledCount[$site] === 1);
    }

    /**
     * @param array $values
     * @param int $remotePostId
     */
    private function updatePostMeta(
        array $values,
        int $remotePostId,
        PersistentAdminNotices $notice
    ) {

        $fieldsKeys = [
            MetaboxFields::FIELD_TITLE,
            MetaboxFields::FIELD_META_DESCRIPTION,
            MetaboxFields::FIELD_FOCUS_KEYPHRASE,
            MetaboxFields::FIELD_CANONICAL,
        ];

        foreach ($fieldsKeys as $key) {
            $this->updateMeta($remotePostId, $key, $values[$key] ?? '');
        }
    }

    private function updateMeta(int $remotePostId, string $key, string $value)
    {
        if ('' === $value) {
            return;
        }

        update_post_meta($remotePostId, "_{$key}", $value);
    }

    /**
     * @param Request $request
     * @return array
     */
    private function allFieldsValues(Request $request): array
    {
        $fields = [];
        $allTabs = $this->fields->allFieldsTabs();
        /** @var Post\MetaboxTab $tab */
        foreach ($allTabs as $tab) {
            $fields += $this->tabFieldsValues($tab, $request);
        }

        return $fields;
    }

    /**
     * @param Post\MetaboxTab $tab
     * @param Request $request
     * @return array
     */
    private function tabFieldsValues(Post\MetaboxTab $tab, Request $request): array
    {
        $fields = [];
        if (!$tab->enabled($this->postRelationshipContext)) {
            return $fields;
        }

        $tabFields = $tab->fields();
        /** @var Post\MetaboxField $field */
        foreach ($tabFields as $field) {
            if ($field->enabled($this->postRelationshipContext)) {
                $fields[$field->key()] = $field->requestValue($request, $this->fieldsHelper);
            }
        }

        return $fields;
    }
}
