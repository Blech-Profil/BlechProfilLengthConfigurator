<?php

namespace BlechProfilLengthConfigurator\Containers;

use Plenty\Plugin\ConfigRepository;
use Plenty\Plugin\Http\Request;
use Plenty\Plugin\Templates\Twig;

class LengthConfiguratorContainer
{
    public function call(Twig $twig, $args = []): string
    {
        $config = pluginApp(ConfigRepository::class);
        $request = pluginApp(Request::class);

        $itemId = 0;
        $variationId = 0;

        // Primary source: current storefront URL, e.g. ..._260_14665
        $requestUri = (string) $request->getRequestUri();
        if (preg_match('/_(\d+)_(\d+)(?:\/)?(?:\?.*)?$/', $requestUri, $matches)) {
            $itemId = (int) $matches[1];
            $variationId = (int) $matches[2];
        }

        // Fallback: object passed to the SingleItem layout container.
        $item = $this->normalizeArgs($args);
        if ($itemId <= 0) {
            $itemId = $this->extractItemId($item);
        }
        if ($variationId <= 0) {
            $variationId = $this->extractVariationId($item);
        }

        // Fail closed: render absolutely nothing unless this article is enabled.
        if ($itemId <= 0 || !$this->isItemEnabled($itemId, $config)) {
            return '';
        }

        return $twig->render('BlechProfilLengthConfigurator::content.LengthConfigurator', [
            'contextItemId' => $itemId,
            'contextVariationId' => $variationId,
            'minLength' => (int) $config->get('BlechProfilLengthConfigurator.length.minLength', 50),
            'maxLength' => (int) $config->get('BlechProfilLengthConfigurator.length.maxLength', 6000),
            'sawKerf' => (int) $config->get('BlechProfilLengthConfigurator.length.sawKerf', 4)
        ]);
    }

    private function normalizeArgs($args): array
    {
        if (is_array($args)) {
            if (isset($args['variation']) || isset($args['ids']) || isset($args['item'])) {
                return $args;
            }

            if (isset($args[0])) {
                if (is_array($args[0])) {
                    return $args[0];
                }

                if (is_object($args[0])) {
                    $decoded = json_decode(json_encode($args[0]), true);
                    return is_array($decoded) ? $decoded : [];
                }
            }

            return $args;
        }

        if (is_object($args)) {
            $decoded = json_decode(json_encode($args), true);
            return is_array($decoded) ? $decoded : [];
        }

        if (is_string($args) && $args !== '') {
            $decoded = json_decode($args, true);
            return is_array($decoded) ? $decoded : [];
        }

        return [];
    }

    private function extractItemId(array $item): int
    {
        if (isset($item['variation']) && is_array($item['variation']) && isset($item['variation']['itemId'])) {
            return (int) $item['variation']['itemId'];
        }

        if (isset($item['ids']) && is_array($item['ids']) && isset($item['ids']['itemId'])) {
            return (int) $item['ids']['itemId'];
        }

        if (isset($item['item']) && is_array($item['item']) && isset($item['item']['id'])) {
            return (int) $item['item']['id'];
        }

        return 0;
    }

    private function extractVariationId(array $item): int
    {
        if (isset($item['variation']) && is_array($item['variation']) && isset($item['variation']['id'])) {
            return (int) $item['variation']['id'];
        }

        if (isset($item['ids']) && is_array($item['ids']) && isset($item['ids']['variationId'])) {
            return (int) $item['ids']['variationId'];
        }

        return 0;
    }

    private function isItemEnabled(int $itemId, ConfigRepository $config): bool
    {
        $value = (string) $config->get('BlechProfilLengthConfigurator.length.enabledItemIds', '260');
        $ids = array_filter(array_map('trim', explode(',', $value)));

        foreach ($ids as $id) {
            if ((int) $id === $itemId) {
                return true;
            }
        }

        return false;
    }
}
