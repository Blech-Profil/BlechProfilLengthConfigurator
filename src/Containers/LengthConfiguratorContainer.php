<?php

namespace BlechProfilLengthConfigurator\Containers;

use Plenty\Plugin\ConfigRepository;
use Plenty\Plugin\Templates\Twig;

class LengthConfiguratorContainer
{
    public function call(Twig $twig, $args = []): string
    {
        $item = isset($args[0]) && is_array($args[0]) ? $args[0] : [];
        $variation = isset($item['variation']) && is_array($item['variation']) ? $item['variation'] : [];

        $variationNumber = isset($variation['number']) ? (string) $variation['number'] : '';
        $variationId = isset($variation['id']) ? (int) $variation['id'] : 0;
        $itemId = isset($variation['itemId']) ? (int) $variation['itemId'] : 0;

        if ($itemId <= 0 && isset($item['ids']['itemId'])) {
            $itemId = (int) $item['ids']['itemId'];
        }

        if ($variationNumber === '' || !preg_match('/^(.+)_([0-9]{2,6})$/', $variationNumber, $matches)) {
            return '';
        }

        $config = pluginApp(ConfigRepository::class);
        $enabledValue = (string) $config->get('BlechProfilLengthConfigurator.length.enabledPrefixes', 'ERD0204305');
        $prefixes = array_filter(array_map('trim', explode(',', $enabledValue)));
        $stem = $matches[1];
        $enabled = false;

        foreach ($prefixes as $prefix) {
            if ($prefix !== '' && strpos($stem, $prefix) === 0) {
                $enabled = true;
                break;
            }
        }

        if (!$enabled) {
            return '';
        }

        return $twig->render('BlechProfilLengthConfigurator::content.LengthConfigurator', [
            'itemId' => $itemId,
            'variationId' => $variationId,
            'variationNumber' => $variationNumber,
            'stem' => $stem,
            'minLength' => (int) $config->get('BlechProfilLengthConfigurator.length.minLength', 50),
            'maxLength' => (int) $config->get('BlechProfilLengthConfigurator.length.maxLength', 6000),
            'sawKerf' => (int) $config->get('BlechProfilLengthConfigurator.length.sawKerf', 4)
        ]);
    }
}
