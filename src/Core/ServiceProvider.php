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

namespace Inpsyde\MultilingualPress\YoastSeoSync\Core;

use Inpsyde\MultilingualPress\Framework\Service\ServiceProvider as MlpServiceProvider;
use Inpsyde\MultilingualPress\Framework\Service\Container;
use Inpsyde\MultilingualPress\YoastSeoSync\PluginProperties;

/**
 * Service provider for all Core objects.
 *
 * phpcs:disable Inpsyde.CodeQuality.FunctionLength.TooLong
 */
final class ServiceProvider implements MlpServiceProvider
{
    /**
     * @inheritdoc
     */
    public function register(Container $container)
    {
        $container->shareValue(
            PluginProperties::class,
            new PluginProperties(\dirname(__DIR__))
        );
    }
}
