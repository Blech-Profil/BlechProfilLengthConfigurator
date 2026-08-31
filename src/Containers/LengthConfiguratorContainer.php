<?php

namespace BlechProfilLengthConfigurator\Containers;

use Plenty\Plugin\ConfigRepository;
use Plenty\Plugin\Templates\Twig;

class LengthConfiguratorContainer
{
    public function call(Twig $twig, $args = []): string
    {
        $item = [];

        if (is_array($args) && isset($args['variation'])) {
            $item = $args;
        } elseif (is_array($args) && isset($args[0]) && is_array($args[0])) {
            $item = $args[0];
        }

        $variation = isset($item['variation']) && is_array($item['variation'])
            ? $item['variation']
            : [];

        $variationId = isset($variation['id']) ? (int) $variation['id'] : 0;
        $itemId = isset($variation['itemId']) ? (int) $variation['itemId'] : 0;

        if ($itemId <= 0 && isset($item['ids']) && is_array($item['ids']) && isset($item['ids']['itemId'])) {
            $itemId = (int) $item['ids']['itemId'];
        }

        if ($itemId <= 0 && isset($item['item']) && is_array($item['item']) && isset($item['item']['id'])) {
            $itemId = (int) $item['item']['id'];
        }

        $config = pluginApp(ConfigRepository::class);

        return $twig->render('BlechProfilLengthConfigurator::content.LengthConfigurator', [
            'contextItemId' => $itemId,
            'contextVariationId' => $variationId,
            'enabledItemIds' => (string) $config->get('BlechProfilLengthConfigurator.length.enabledItemIds', '260'),
            'minLength' => (int) $config->get('BlechProfilLengthConfigurator.length.minLength', 50),
            'maxLength' => (int) $config->get('BlechProfilLengthConfigurator.length.maxLength', 6000),
            'sawKerf' => (int) $config->get('BlechProfilLengthConfigurator.length.sawKerf', 4)
        ]);
    }
}
