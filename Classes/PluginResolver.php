<?php

namespace DigiComp\League\Flysystem;

use DigiComp\FlowObjectResolving\ResolverTrait;
use League\Flysystem\PluginInterface;

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

    protected function getManagedNamespace(string $packageName = ''): string
    {
        if ($packageName === static::getDefaultPackageKey()) {
            return 'Plugin\\';
        }
        return 'FlysystemPlugin\\';
    }

    protected static function getDefaultPackageKey(): string
    {
        return 'league.flysystem';
    }
}
