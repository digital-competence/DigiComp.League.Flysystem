<?php
namespace DigiComp\Flysystem;

use Doctrine\ORM\Mapping as ORM;
use League\Flysystem\Filesystem;
use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Object\ObjectManagerInterface;
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

    public function create($filesystemAdapterName, $filesystemProperties, $plugins = array()) {
        $filesystem = new Filesystem(new $filesystemAdapterName($filesystemProperties));
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

    public function resolvePlugin($pluginName) {
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
