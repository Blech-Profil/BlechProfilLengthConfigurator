<?php

namespace BlechProfilLengthConfigurator\Containers;

use Plenty\Plugin\Templates\Twig;

class LengthConfiguratorScriptContainer
{
    public function call(Twig $twig): string
    {
        return $twig->render('BlechProfilLengthConfigurator::content.LengthConfiguratorScript');
    }
}
