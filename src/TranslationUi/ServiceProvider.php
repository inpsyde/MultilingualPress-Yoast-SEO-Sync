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

namespace Inpsyde\MultilingualPress\YoastSeoSync\TranslationUi;

use Inpsyde\MultilingualPress\Core\Entity\ActivePostTypes;
use Inpsyde\MultilingualPress\Framework\Admin\PersistentAdminNotices;
use Inpsyde\MultilingualPress\Framework\Http\Request;
use Inpsyde\MultilingualPress\Framework\Service\BootstrappableServiceProvider;
use Inpsyde\MultilingualPress\Framework\Service\Container;
use Inpsyde\MultilingualPress\TranslationUi\MetaboxFieldsHelper;
use Inpsyde\MultilingualPress\TranslationUi\Post\Metabox as Box;
use Inpsyde\MultilingualPress\YoastSeoSync\TranslationUi\Post\MetaboxAction;
use Inpsyde\MultilingualPress\YoastSeoSync\TranslationUi\Post\MetaboxFields;
use Inpsyde\MultilingualPress\TranslationUi\Post;

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
            MetaboxFields::class,
            function (): MetaboxFields {
                return new MetaboxFields();
            }
        );
    }

    /**
     * @inheritdoc
     */
    public function bootstrap(Container $container)
    {
        $metaboxFields = $container[MetaboxFields::class];

        add_filter(Box::HOOK_PREFIX . 'tabs', function (array $tabs) use ($metaboxFields) {
            return array_merge($tabs, $metaboxFields->allFieldsTabs());
        });

        add_action(
            Post\MetaboxAction::ACTION_METABOX_AFTER_RELATE_POSTS,
            function (
                Post\RelationshipContext $context,
                Request $request,
                PersistentAdminNotices $notice
            ) use (
                $metaboxFields,
                $container
            ) {
                $metaboxAction = new MetaboxAction(
                    $metaboxFields,
                    new MetaboxFieldsHelper($context->remoteSiteId()),
                    $context,
                    $container[ActivePostTypes::class]
                );
                $metaboxAction->save($request, $notice);
            },
            10,
            3
        );
    }
}
