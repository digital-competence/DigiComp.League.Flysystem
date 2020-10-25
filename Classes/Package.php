<?php

namespace DigiComp\League\Flysystem;

use Neos\Flow\Configuration\ConfigurationManager;
use Neos\Flow\Core\Bootstrap;
use Neos\Flow\Package\Package as BasePackage;

class Package extends BasePackage
{
    const CONFIGURATION_TYPE_FILESYSTEM = 'Filesystem';

    /**
     * @param Bootstrap $bootstrap
     */
    public function boot(Bootstrap $bootstrap)
    {
        parent::boot($bootstrap);

        $dispatcher = $bootstrap->getSignalSlotDispatcher();
        $dispatcher->connect(
            ConfigurationManager::class,
            'configurationManagerReady',
            function (ConfigurationManager $configurationManager) {
                $configurationManager->registerConfigurationType(
                    static::CONFIGURATION_TYPE_FILESYSTEM,
                    ConfigurationManager::CONFIGURATION_PROCESSING_TYPE_DEFAULT,
                    true
                );
            }
        );
    }
}
