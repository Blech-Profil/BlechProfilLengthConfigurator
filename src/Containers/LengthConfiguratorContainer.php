<?php

namespace BlechProfilLengthConfigurator\Containers;

use Plenty\Plugin\ConfigRepository;
use Plenty\Plugin\Templates\Twig;

class LengthConfiguratorContainer
{
    public function call(Twig $twig, $args = []): string
    {
        /*
         * plentyShop LTS passes item.documents[0].data directly to
         * Ceres::SingleItem.BeforeAddToBasket. Depending on the container
         * invocation, the value can arrive directly or as the first argument.
         */
        $item = [];

        if (is_array($args) && isset($args['variation'])) {
            $item = $args;
        } elseif (is_array($args) && isset($args[0]) && is_array($args[0])) {
            $item = $args[0];
        }

        $variation = isset($item['variation']) && is_array($item['variation'])
            ? $item['variation']
            : [];

        $variationNumber = isset($variation['number']) ? (string) $variation['number'] : '';
        $variationId = isset($variation['id']) ? (int) $variation['id'] : 0;
        $itemId = isset($variation['itemId']) ? (int) $variation['itemId'] : 0;

        if ($itemId <= 0 && isset($item['ids']) && is_array($item['ids']) && isset($item['ids']['itemId'])) {
            $itemId = (int) $item['ids']['itemId'];
        }

        if ($itemId <= 0 && isset($item['item']) && is_array($item['item']) && isset($item['item']['id'])) {
            $itemId = (int) $item['item']['id'];
        }

        if ($variationNumber === '') {
            return '<!-- BP-LengthConfigurator 0.1.2: container reached, but variation.number missing -->';
        }

        if (!preg_match('/^(.+)_([0-9]{2,6})$/', $variationNumber, $matches)) {
            return '<!-- BP-LengthConfigurator 0.1.2: variation number does not match STAMM_LAENGE -->';
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
            return '<!-- BP-LengthConfigurator 0.1.2: stem not enabled -->';
        }

        return '<!-- BP-LengthConfigurator 0.1.2: active -->' .
            $twig->render('BlechProfilLengthConfigurator::content.LengthConfigurator', [
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
