<?php # -*- coding: utf-8 -*-

declare(strict_types=1);

namespace Inpsyde\MultilingualPress\YoastSeoSync\TranslationUi;

use Inpsyde\MultilingualPress\Core\Entity\ActivePostTypes;
use Inpsyde\MultilingualPress\Framework\Admin\PersistentAdminNotices;
use Inpsyde\MultilingualPress\Framework\Http\Request;
use Inpsyde\MultilingualPress\Framework\Service\BootstrappableServiceProvider;
use Inpsyde\MultilingualPress\Framework\Service\Container;
use Inpsyde\MultilingualPress\TranslationUi\MetaboxFieldsHelper;
use Inpsyde\MultilingualPress\TranslationUi\Post;
use Inpsyde\MultilingualPress\TranslationUi\Post\Metabox as PostBox;
use Inpsyde\MultilingualPress\TranslationUi\Term\RelationshipContext;
use Inpsyde\MultilingualPress\YoastSeoSync\TranslationUi\Post\MetaboxAction as PostMetaboxAction;
use Inpsyde\MultilingualPress\YoastSeoSync\TranslationUi\Post\MetaboxFields as PostMetaboxFields;
use Inpsyde\MultilingualPress\TranslationUi\Term;
use Inpsyde\MultilingualPress\TranslationUi\Term\Metabox as TermBox;
use Inpsyde\MultilingualPress\YoastSeoSync\TranslationUi\Term\MetaboxAction as TermMetaboxAction;
use Inpsyde\MultilingualPress\YoastSeoSync\TranslationUi\Term\MetaboxFields as TermMetaboxFields;

/**
 * Class ServiceProvider
 *
 * @package Inpsyde\MultilingualPress\YoastSeoSync\TranslationUi
 */
final class ServiceProvider implements BootstrappableServiceProvider
{
    /**
     * @inheritdoc
     */
    public function register(Container $container)
    {
        $container->addService(
            PostMetaboxFields::class,
            function (): PostMetaboxFields {
                return new PostMetaboxFields();
            }
        );

        $container->addService(
            TermMetaboxFields::class,
            function (): TermMetaboxFields {
                return new TermMetaboxFields();
            }
        );
    }

    /**
     * @inheritdoc
     * phpcs:disable Inpsyde.CodeQuality.FunctionLength.TooLong
     */
    public function bootstrap(Container $container)
    {
        // phpcs:enable
        $postMetaboxFields = $container[PostMetaboxFields::class];

        add_filter(
            PostBox::HOOK_PREFIX . 'tabs',
            function (array $tabs) use ($postMetaboxFields) {
                return array_merge($tabs, $postMetaboxFields->allFieldsTabs());
            }
        );

        add_action(
            Post\MetaboxAction::ACTION_METABOX_AFTER_RELATE_POSTS,
            function (
                Post\RelationshipContext $context,
                Request $request,
                PersistentAdminNotices $notice
            ) use (
                $postMetaboxFields,
                $container
            ) {
                $metaboxAction = new PostMetaboxAction(
                    $postMetaboxFields,
                    new MetaboxFieldsHelper($context->remoteSiteId()),
                    $context,
                    $container[ActivePostTypes::class]
                );
                $metaboxAction->save($request, $notice);
            },
            10,
            3
        );

        if (defined('\\Inpsyde\\MultilingualPress\\TranslationUi\\Term\\MetaboxAction::ACTION_METABOX_AFTER_RELATE_TERMS')) {
            $termMetaboxFields = $container[TermMetaboxFields::class];

            add_filter(
                TermBox::HOOK_PREFIX . 'tabs',
                function (array $tabs) use ($termMetaboxFields) {
                    return array_merge($tabs, $termMetaboxFields->allFieldsTabs());
                }
            );

            add_action(
                Term\MetaboxAction::ACTION_METABOX_AFTER_RELATE_TERMS,
                function (
                    Term\RelationshipContext $context,
                    Request $request,
                    PersistentAdminNotices $notice
                ) use (
                    $termMetaboxFields,
                    $container
                ) {
                    $metaboxAction = new TermMetaboxAction(
                        $termMetaboxFields,
                        new MetaboxFieldsHelper($context->remoteSiteId()),
                        $context
                    );
                    $metaboxAction->save($request, $notice);
                },
                10,
                3
            );
        }
    }
}
