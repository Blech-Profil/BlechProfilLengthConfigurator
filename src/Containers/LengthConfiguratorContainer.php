<?php

namespace BlechProfilLengthConfigurator\Containers;

use Plenty\Plugin\ConfigRepository;
use Plenty\Plugin\Templates\Twig;

class LengthConfiguratorContainer
{
    public function call(Twig $twig, $args = []): string
    {
        $config = pluginApp(ConfigRepository::class);

        return $twig->render('BlechProfilLengthConfigurator::content.LengthConfigurator', [
            'activationPropertyId' => (int) $config->get('BlechProfilLengthConfigurator.length.activationPropertyId', 48),
            'activationSelectionId' => (int) $config->get('BlechProfilLengthConfigurator.length.activationSelectionId', 71),
            'minLength' => (int) $config->get('BlechProfilLengthConfigurator.length.minLength', 50),
            'maxLength' => (int) $config->get('BlechProfilLengthConfigurator.length.maxLength', 6000),
            'sawKerf' => (int) $config->get('BlechProfilLengthConfigurator.length.sawKerf', 4)
        ]);
    }
}
