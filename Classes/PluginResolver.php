<?php

namespace DigiComp\League\Flysystem;

use DigiComp\FlowObjectResolving\Exception as ResolvingException;
use DigiComp\FlowObjectResolving\ResolverTrait;
use League\Flysystem\PluginInterface;
use Neos\Flow\Package\Exception\UnknownPackageException;

class PluginResolver
{
    use ResolverTrait;

    protected static function getManagedInterface(): string
    {
        return PluginInterface::class;
    }

    protected function getManagedNamespace(string $packageName): string
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

    /**
     * @param string $pluginName
     *
     * @return PluginInterface
     * @throws ResolvingException
     * @throws UnknownPackageException
     */
    public function create(string $pluginName): PluginInterface
    {
        $className = $this->resolveObjectName($pluginName);
        return new $className();
    }
}
