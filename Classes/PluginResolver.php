<?php

namespace DigiComp\League\Flysystem;

use DigiComp\FlowObjectResolving\ResolverTrait;
use League\Flysystem\PluginInterface;
use Neos\Flow\ObjectManagement\ObjectManagerInterface;

/**
 * @method PluginInterface create(string $pluginName)
 */
class PluginResolver
{
    use ResolverTrait;

    protected static function getManagedInterface(): string
    {
        return PluginInterface::class;
    }

    protected static function getManagedNamespace(): string
    {
        return 'FlysystemPlugin\\';
    }

    protected static function getDefaultPackageKey(ObjectManagerInterface $objectManager): string
    {
        return 'league.flysystem';
    }

    protected static function getDefaultNamespace(): string
    {
        return 'Plugin\\';
    }
}
