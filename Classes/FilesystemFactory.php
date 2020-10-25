<?php

namespace DigiComp\League\Flysystem;

use DigiComp\FlowObjectResolving\Exception as ResolvingException;
use League\Flysystem\AdapterInterface;
use League\Flysystem\Filesystem;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Configuration\Exception\InvalidConfigurationException;
use Neos\Flow\ObjectManagement\ObjectManagerInterface;
use Neos\Utility\ObjectAccess;
use Psr\Log\LoggerInterface;

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
     * @Flow\InjectConfiguration(type="Filesystem")
     * @var array
     */
    protected $filesystemConfiguration;

    /**
     * @Flow\Inject
     * @var PluginResolver
     */
    protected $pluginResolver;

    /**
     * @Flow\Inject
     * @var LoggerInterface
     */
    protected $systemLogger;

    /**
     * @param array $filesystemAdapter
     * @param array $plugins
     * @return Filesystem
     * @throws InvalidConfigurationException
     * @throws \ReflectionException
     * @throws ResolvingException
     */
    public function create(array $filesystemAdapter, $plugins = []): Filesystem
    {
        $adapterName = $filesystemAdapter['adapter'];
        unset($filesystemAdapter['adapter']);

        $filesystemConfig = $filesystemAdapter['filesystemConfig'] ?? [];
        unset($filesystemAdapter['filesystemConfig']);

        if (isset($filesystemAdapter['adapterArguments'])) {
            $adapterArguments = $filesystemAdapter['adapterArguments'];
        } else {
            $this->systemLogger->notice('Not using adapterArguments is deprecated and will be removed in next major');
            $adapterArguments = $filesystemAdapter;
        }

        $class = new \ReflectionClass($adapterName);
        $constructor = $class->getConstructor();

        $arguments = [];
        foreach ($constructor->getParameters() as $parameter) {
            if (isset($adapterArguments[$parameter->getName()])) {
                $arguments[] = $adapterArguments[$parameter->getName()];
                unset($adapterArguments[$parameter->getName()]);
            } elseif (! $parameter->isOptional()) {
                throw new InvalidConfigurationException(
                    'Missing Parameter of ' . $adapterName . ': ' . $parameter->getName()
                );
            }
        }

        /* @var AdapterInterface $adapter */
        $adapter = $class->newInstanceArgs($arguments);
        foreach ($adapterArguments as $key => $val) {
            if (ObjectAccess::isPropertySettable($adapter, $key)) {
                ObjectAccess::setProperty($adapter, $key, $val);
            } else {
                throw new InvalidConfigurationException('Could not set Parameter ' . $key . ' to ' . $adapterName);
            }
        }

        $filesystem = new Filesystem($adapter, $filesystemConfig);
        foreach ($plugins as $plugin) {
            $filesystem->addPlugin($this->pluginResolver->create($plugin));
        }

        return $filesystem;
    }

    /**
     * @param string $filesystemName
     *
     * @return Filesystem
     * @throws InvalidConfigurationException
     * @throws ResolvingException
     * @throws \ReflectionException
     */
    public function createNamedFilesystem(string $filesystemName): Filesystem
    {
        if (! isset($this->filesystemConfiguration[$filesystemName])) {
            throw new InvalidConfigurationException('Filesystem name "' . $filesystemName . '" is not known', 1603582487);
        }
        $configuration = $this->filesystemConfiguration[$filesystemName];
        $plugins = $configuration['plugins'];
        unset($configuration['plugins']);
        return $this->create($configuration, $plugins);
    }
}
