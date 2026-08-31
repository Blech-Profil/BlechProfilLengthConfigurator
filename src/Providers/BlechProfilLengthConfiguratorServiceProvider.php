<?php

namespace BlechProfilLengthConfigurator\Providers;

use Plenty\Plugin\ServiceProvider;

class BlechProfilLengthConfiguratorServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->getApplication()->register(BlechProfilLengthConfiguratorRouteServiceProvider::class);
    }
}
