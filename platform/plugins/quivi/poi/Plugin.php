<?php namespace Quivi\Poi;

use System\Classes\PluginBase;

class Plugin extends PluginBase
{
    public function registerComponents()
    {
    }

    public function registerSettings()
    {
    }

    public function register()
    {
        $this->registerConsoleCommand('Quivi.Poi:Sync', 'Quivi\Poi\Console\Sync');
    }
}
