<?php

namespace DigiComp\League\Flysystem;

use DigiComp\FlowObjectResolving\Exception as ResolvingException;
use League\Flysystem\AdapterInterface;
use League\Flysystem\Filesystem;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Configuration\Exception\InvalidConfigurationException;
use Neos\Flow\ObjectManagement\ObjectManagerInterface;
use Neos\Utility\ObjectAccess;

/**
 * @Flow\Scope("singleton")
 */
class FilesystemFactory
{
    /**
     * @Flow\Inject
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @Flow\Inject
     * @var PluginResolver
     */
    protected $pluginResolver;

    /**
     * @param array $filesystemAdapter
     * @param array $plugins
     * @return Filesystem
     * @throws InvalidConfigurationException
     * @throws \ReflectionException
     * @throws ResolvingException
     */
    public function create(array $filesystemAdapter, $plugins = [])
    {
        $adapterName = $filesystemAdapter['adapter'];
        unset($filesystemAdapter['adapter']);

        $class = new \ReflectionClass($adapterName);
        $constructor = $class->getConstructor();

        $arguments = [];
        foreach ($constructor->getParameters() as $parameter) {
            if (isset($filesystemAdapter[$parameter->getName()])) {
                $arguments[] = $filesystemAdapter[$parameter->getName()];
                unset($filesystemAdapter[$parameter->getName()]);
            } elseif (! $parameter->isOptional()) {
                throw new InvalidConfigurationException(
                    'Missing Parameter of ' . $adapterName . ': ' . $parameter->getName()
                );
            }
        }

        /* @var AdapterInterface $adapter */
        $adapter = $class->newInstanceArgs($arguments);
        foreach ($filesystemAdapter as $key => $val) {
            if (ObjectAccess::isPropertySettable($adapter, $key)) {
                ObjectAccess::setProperty($adapter, $key, $val);
            } else {
                throw new InvalidConfigurationException('Could not set Parameter ' . $key . ' to ' . $adapterName);
            }
        }

        $filesystem = new Filesystem($adapter);
        foreach ($plugins as $plugin) {
            $pluginClass = $this->resolvePlugin($plugin);
            $filesystem->addPlugin(new $pluginClass());
        }

        return $filesystem;
    }
}
