<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Content.db8bci
 *
 * @author      Peter Martin <joomla@db8.nl>
 * @copyright   Copyright (C) 2025 Peter Martin. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @link        https://db8.nl
 */

defined('_JEXEC') or die;

use Db8\Plugin\Content\Db8Bci\Extension\Db8Bci;
use Joomla\CMS\Extension\PluginInterface;
use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;
use Joomla\Event\DispatcherInterface;

return new class () implements ServiceProviderInterface {
    /**
     * Registers the service provider with a DI container.
     *
     * @param Container $container The DI container.
     *
     * @return  void
     *
     * @since   1.0.0
     */
    public function register(Container $container): void
    {
        $container->set(
            PluginInterface::class,
            function (Container $container) {
                $plugin = new Db8Bci(
                    $container->get(DispatcherInterface::class),
                    (array)PluginHelper::getPlugin('content', 'db8bci')
                );
                $plugin->setApplication(Factory::getApplication());
                return $plugin;
            }
        );
    }
};
