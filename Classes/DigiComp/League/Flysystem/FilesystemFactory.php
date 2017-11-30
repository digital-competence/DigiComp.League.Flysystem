<?php
namespace DigiComp\League\Flysystem;

use League\Flysystem\AdapterInterface;
use League\Flysystem\Filesystem;
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
     * TODO: What to do with local filesystem?
     *
     * @return Filesystem
     * @throws InvalidConfigurationException
     */
    public function create($filesystemAdapter, $plugins = []) {
        $adapterName = $filesystemAdapter['adapter'];
        unset($filesystemAdapter['adapter']);

        $class = new \ReflectionClass($adapterName);
        $constructor = $class->getConstructor();

        $arguments = [];
        foreach($constructor->getParameters() as $parameter) {
            if (isset($filesystemAdapter[$parameter->getName()])) {
                $arguments[] = $filesystemAdapter[$parameter->getName()];
                unset($filesystemAdapter[$parameter->getName()]);
            } elseif(!$parameter->isOptional()) {
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
     * @param ObjectManagerInterface $objectManager
     * @Flow\CompileStatic
     * @return array
     */
    public static function getPlugins($objectManager) {
        /** @var ReflectionService $reflectionService */
        $reflectionService = $objectManager->get('TYPO3\Flow\Reflection\ReflectionService');
        $classNames = $reflectionService->getAllImplementationClassNamesForInterface('League\Flysystem\PluginInterface');
        return array_flip($classNames);
    }

    /**
     * Searches for plugin classes, resolves Plugins without Package identifier in League\Flysystem
     * Names like DigiComp.Package:Special are resolved to \DigiComp\Package\FlysystemPlugin\Special
     *
     * @param string $pluginName
     *
     * @return bool|string
     */
    protected function resolvePlugin($pluginName) {
        $plugins = $this->getPlugins($this->objectManager);
        if (strpos($pluginName, ':') !== FALSE) {
            list($packageName, $packagePluginName) = explode(':', $pluginName);
            $possibleClassName = sprintf('%s\FlysystemPlugin\%s', str_replace('.', '\\', $packageName), $packagePluginName);
        } else {
            $possibleClassName = sprintf('League\Flysystem\Plugin\%s', $pluginName);
        }
        if ($this->objectManager->isRegistered($possibleClassName) && isset($plugins[$possibleClassName])) {
            return $possibleClassName;
        }
        return false;
    }
}
