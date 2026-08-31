<?php

namespace BlechProfilLengthConfigurator\Services;

use IO\Services\ItemService;
use Plenty\Plugin\ConfigRepository;

class VariationEligibilityService
{
    /** @var ItemService */
    private $itemService;

    /** @var ConfigRepository */
    private $config;

    public function __construct(ItemService $itemService, ConfigRepository $config)
    {
        $this->itemService = $itemService;
        $this->config = $config;
    }

    public function check(int $variationId): array
    {
        if ($variationId <= 0) {
            return $this->result(false, 'Ungültige Varianten-ID.', 0, 0);
        }

        $propertyId = (int) $this->config->get(
            'BlechProfilLengthConfigurator.length.activationPropertyId',
            48
        );
        $selectionId = (int) $this->config->get(
            'BlechProfilLengthConfigurator.length.activationSelectionId',
            71
        );

        if ($propertyId <= 0 || $selectionId <= 0) {
            return $this->result(false, 'Eigenschafts-ID oder Ja-Auswahl-ID ist im Plugin nicht korrekt hinterlegt.', $propertyId, $selectionId);
        }

        try {
            $variationResult = $this->itemService->getVariation($variationId);
        } catch (\Throwable $e) {
            return $this->result(false, 'Die Variante konnte nicht aus dem plentyShop geladen werden.', $propertyId, $selectionId);
        }

        if ($this->containsPropertySelection($variationResult, $propertyId, $selectionId)) {
            return $this->result(true, 'Zuschnitt möglich = Ja.', $propertyId, $selectionId);
        }

        return $this->result(false, 'Zuschnitt ist für diese Variante nicht mit dem Ja-Auswahlwert freigeschaltet.', $propertyId, $selectionId);
    }

    private function containsPropertySelection($node, int $propertyId, int $selectionId): bool
    {
        if (!is_array($node)) {
            return false;
        }

        if (
            isset($node['propertyId'])
            && (int) $node['propertyId'] === $propertyId
            && $this->containsSelectionId($node, $selectionId)
        ) {
            return true;
        }

        foreach ($node as $value) {
            if (is_array($value) && $this->containsPropertySelection($value, $propertyId, $selectionId)) {
                return true;
            }
        }

        return false;
    }

    private function containsSelectionId($node, int $selectionId): bool
    {
        if (!is_array($node)) {
            return false;
        }

        if (isset($node['selectionId']) && (int) $node['selectionId'] === $selectionId) {
            return true;
        }

        foreach ($node as $value) {
            if (is_array($value) && $this->containsSelectionId($value, $selectionId)) {
                return true;
            }
        }

        return false;
    }

    private function result(bool $enabled, string $message, int $propertyId, int $selectionId): array
    {
        return [
            'success' => true,
            'enabled' => $enabled,
            'message' => $message,
            'propertyId' => $propertyId,
            'selectionId' => $selectionId,
            'variationId' => 0
        ];
    }
}
