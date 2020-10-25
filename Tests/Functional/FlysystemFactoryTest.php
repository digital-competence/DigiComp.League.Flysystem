<?php

namespace DigiComp\League\Flysystem\Tests\Functional;

use DigiComp\League\Flysystem\FilesystemFactory;
use League\Flysystem\Filesystem;
use Neos\Flow\Configuration\ConfigurationManager;
use Neos\Flow\Tests\FunctionalTestCase;

class FlysystemFactoryTest extends FunctionalTestCase
{
    /**
     * @test
     */
    public function itCreatesAFilesystem()
    {
        $configurationManager = $this->objectManager->get(ConfigurationManager::class);
        $configuration = $configurationManager->getConfiguration(
            ConfigurationManager::CONFIGURATION_TYPE_SETTINGS,
            'DigiComp.League.Flysystem.testFilesystem'
        );
        $deprecatedConfiguration = $configurationManager->getConfiguration(
            ConfigurationManager::CONFIGURATION_TYPE_SETTINGS,
            'DigiComp.League.Flysystem.deprecatedTestFilesystem'
        );
        $factory = $this->objectManager->get(FilesystemFactory::class);
        $filesystem = $factory->create($configuration, ['ListPaths', 'ListFiles']);
        $deprecatedFilesystem = $factory->create($deprecatedConfiguration, ['ListPaths', 'ListFiles']);
        $this->assertInstanceOf(Filesystem::class, $filesystem);
        $this->assertInstanceOf(Filesystem::class, $deprecatedFilesystem);

        $namedFilesystem = $factory->createNamedFilesystem('DigiComp.League.Flysystem.TestingFilesystem');
        $this->assertEquals($filesystem, $namedFilesystem);
        $this->assertEquals($filesystem, $deprecatedFilesystem);
    }
}
