<?php

namespace BlechProfilLengthConfigurator\Providers;

use Plenty\Plugin\RouteServiceProvider;
use Plenty\Plugin\Routing\Router;

class BlechProfilLengthConfiguratorRouteServiceProvider extends RouteServiceProvider
{
    public function map(Router $router)
    {
        $router->get(
            'blechprofil-length-configurator/resolve',
            'BlechProfilLengthConfigurator\\Controllers\\LengthConfiguratorController@resolve'
        );

        $router->post(
            'blechprofil-length-configurator/add',
            'BlechProfilLengthConfigurator\\Controllers\\LengthConfiguratorController@addToBasket'
        );
    }
}
