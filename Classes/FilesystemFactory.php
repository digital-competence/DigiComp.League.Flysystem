<?php
namespace DigiComp\League\Flysystem;

use League\Flysystem\AdapterInterface;
use League\Flysystem\Filesystem;
use League\Flysystem\PluginInterface;
use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Configuration\Exception\InvalidConfigurationException;
use TYPO3\Flow\Object\ObjectManagerInterface;
use TYPO3\Flow\Reflection\ObjectAccess;
use TYPO3\Flow\Reflection\ReflectionService;

/**
 * @Flow\Scope("singleton")
 */
class FilesystemFactory
{
    /**
     * @var ObjectManagerInterface
     * @Flow\Inject
     */
    protected $objectManager;

    /**
     * @param array $filesystemAdapter
     * @param array $plugins
     *
     * @return Filesystem
     * @throws InvalidConfigurationException
     */
    public function create($filesystemAdapter, $plugins = [])
    {
        $adapterName = $filesystemAdapter['adapter'];
        unset($filesystemAdapter['adapter']);

        $class = new \ReflectionClass($adapterName);
        $constructor = $class->getConstructor();

        $arguments = [];
        foreach($constructor->getParameters() as $parameter) {
            if (isset($filesystemAdapter[$parameter->getName()])) {
                $arguments[] = $filesystemAdapter[$parameter->getName()];
                unset($filesystemAdapter[$parameter->getName()]);
            } elseif(! $parameter->isOptional()) {
                throw new InvalidConfigurationException('Missing Parameter of ' . $adapterName . ': ' . $parameter->getName());
            }
        }

        /** @var AdapterInterface $adapter */
        $adapter = $class->newInstanceArgs($arguments);
        foreach($filesystemAdapter as $key => $val) {
            if (ObjectAccess::isPropertySettable($adapter, $key)) {
                ObjectAccess::setProperty($adapter, $key, $val);
            } else {
                throw new InvalidConfigurationException('Could not set Parameter ' . $key . ' to ' . $adapterName);
            }
        }

        $filesystem = new Filesystem($adapter);
        foreach($plugins as $plugin) {
            $pluginClass = $this->resolvePlugin($plugin);
            $filesystem->addPlugin(new $pluginClass());
        }

        return $filesystem;
    }

    /**
     * @Flow\CompileStatic
     *
     * @param ObjectManagerInterface $objectManager
     *
     * @return array
     */
    public static function getPlugins($objectManager)
    {
        /** @var ReflectionService $reflectionService */
        $reflectionService = $objectManager->get(ReflectionService::class);
        $classNames = $reflectionService->getAllImplementationClassNamesForInterface(PluginInterface::class);

        return array_flip($classNames);
    }

    /**
     * Searches for plugin classes, resolves plugins without package identifier in League\Flysystem\Plugin.
     * Names like AcMe.Package:MyPlugin are resolved to \AcMe\Package\FlysystemPlugin\MyPlugin.
     *
     * @param string $pluginName
     *
     * @return bool|string
     */
    protected function resolvePlugin($pluginName)
    {
        if (strpos($pluginName, ':') !== false) {
            list($packageName, $packagePluginName) = explode(':', $pluginName);
            $possibleClassName = sprintf('%s\FlysystemPlugin\%s', str_replace('.', '\\', $packageName), $packagePluginName);
        } else {
            $possibleClassName = sprintf('League\Flysystem\Plugin\%s', $pluginName);
        }

        $plugins = $this->getPlugins($this->objectManager);
        if ($this->objectManager->isRegistered($possibleClassName) && isset($plugins[$possibleClassName])) {
            return $possibleClassName;
        }

        return false;
    }
}
