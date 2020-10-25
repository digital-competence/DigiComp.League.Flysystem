<?php

namespace DigiComp\League\Flysystem\Command;

use DigiComp\League\Flysystem\PluginResolver;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Cli\CommandController;

class FlysystemCommandController extends CommandController
{
    /**
     * @Flow\Inject
     * @var PluginResolver
     */
    protected $pluginResolver;

    public function listPluginsCommand()
    {
        foreach ($this->pluginResolver->getAvailableNames() as $availableName) {
            $this->outputLine($availableName);
        }
    }
}
