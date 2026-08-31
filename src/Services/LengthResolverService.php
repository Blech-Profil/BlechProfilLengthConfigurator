<?php

namespace BlechProfilLengthConfigurator\Services;

use IO\Services\ItemService;
use Plenty\Modules\Item\Variation\Contracts\VariationRepositoryContract;
use Plenty\Plugin\ConfigRepository;

class LengthResolverService
{
    /** @var ItemService */
    private $itemService;

    /** @var VariationRepositoryContract */
    private $variationRepository;

    /** @var ConfigRepository */
    private $config;

    public function __construct(
        ItemService $itemService,
        VariationRepositoryContract $variationRepository,
        ConfigRepository $config
    ) {
        $this->itemService = $itemService;
        $this->variationRepository = $variationRepository;
        $this->config = $config;
    }

    public function resolve(int $itemId, int $currentVariationId, int $desiredLength, float $quantity = 1.0): array
    {
        $quantity = max(1.0, $quantity);
        $minLength = (int) $this->config->get('BlechProfilLengthConfigurator.length.minLength', 50);
        $maxLength = (int) $this->config->get('BlechProfilLengthConfigurator.length.maxLength', 6000);
        $sawKerf = (int) $this->config->get('BlechProfilLengthConfigurator.length.sawKerf', 4);

        if ($itemId <= 0) {
            return $this->error('Ungültige Artikel-ID.');
        }

        if (!$this->isItemEnabled($itemId)) {
            return $this->error('Dieser Artikel ist für den Wunschlängen-Konfigurator nicht freigeschaltet.');
        }

        if ($currentVariationId <= 0) {
            return $this->error('Ungültige Varianten-ID.');
        }

        if ($desiredLength < $minLength || $desiredLength > $maxLength) {
            return $this->error('Die Wunschlänge liegt außerhalb des erlaubten Bereichs.');
        }

        // getVariationIds liefert laut IO-Dokumentation aktive und verkaufbare Varianten des Artikels.
        $variationIds = $this->itemService->getVariationIds($itemId);

        if (!is_array($variationIds) || count($variationIds) === 0) {
            return $this->error('Für den Artikel wurden keine aktiven und verkaufbaren Varianten gefunden.');
        }

        $variationIds = array_values(array_unique(array_map('intval', $variationIds)));

        // Die aufgerufene Variante kann wegen Lager-/Shopregeln theoretisch nicht in getVariationIds stecken.
        // Für die Gruppenerkennung laden wir sie dann trotzdem separat dazu.
        $idsToLoad = $variationIds;
        if (!in_array($currentVariationId, $idsToLoad, true)) {
            $idsToLoad[] = $currentVariationId;
        }

        $variations = $this->variationRepository->showMultiple($idsToLoad, []);
        $currentVariationNumber = '';

        foreach ($variations as $variation) {
            if ((int) $variation->id === $currentVariationId) {
                $currentVariationNumber = (string) $variation->number;
                break;
            }
        }

        if ($currentVariationNumber === '') {
            return $this->error('Die Variantennummer der gewählten Variante konnte nicht geladen werden.');
        }

        $parsed = $this->parseVariationNumber($currentVariationNumber);
        if ($parsed === null) {
            return $this->error('Die Variantennummer folgt nicht dem erwarteten Muster STAMM_LAENGE, z. B. ERD0204305_1500. Geladen wurde: ' . $currentVariationNumber);
        }

        $stem = $parsed['stem'];
        if (!$this->isStemEnabled($stem)) {
            return $this->error('Der Artikelstamm ' . $stem . ' ist für den Wunschlängen-Konfigurator nicht freigeschaltet.');
        }

        $requiredPerPiece = $desiredLength + $sawKerf;
        $candidates = [];

        foreach ($variations as $variation) {
            $number = (string) $variation->number;
            $id = (int) $variation->id;
            $candidateParsed = $this->parseVariationNumber($number);

            if ($id <= 0 || $candidateParsed === null || $candidateParsed['stem'] !== $stem) {
                continue;
            }

            if (!in_array($id, $variationIds, true)) {
                continue;
            }

            $rawLength = $candidateParsed['length'];
            if ($rawLength < $requiredPerPiece) {
                continue;
            }

            // Alpha 0.1.4: IO prüft für uns, ob die Variation im Shop grundsätzlich verkaufbar ist.
            // Exakte Nettobestandsmengen je Lager kommen nach dem UI-/Routing-Test wieder dazu.
            if (!$this->itemService->getVariationIsSalable($id)) {
                continue;
            }

            $candidates[] = [
                'variationId' => $id,
                'variationNumber' => $number,
                'externalId' => (string) $variation->externalId,
                'rawLength' => $rawLength,
                'salable' => true,
                'restPerPiece' => max(0, $rawLength - $desiredLength - $sawKerf)
            ];
        }

        if (count($candidates) === 0) {
            return $this->error('Keine passende verkaufbare Lagerlänge gefunden.');
        }

        usort($candidates, function (array $a, array $b): int {
            if ($a['rawLength'] === $b['rawLength']) {
                return $a['variationId'] <=> $b['variationId'];
            }
            return $a['rawLength'] <=> $b['rawLength'];
        });

        $selected = $candidates[0];

        return [
            'success' => true,
            'itemId' => $itemId,
            'sourceVariationId' => $currentVariationId,
            'sourceVariationNumber' => $currentVariationNumber,
            'stem' => $stem,
            'desiredLength' => $desiredLength,
            'quantity' => $quantity,
            'sawKerf' => $sawKerf,
            'requiredPerPiece' => $requiredPerPiece,
            'selected' => $selected,
            'candidateCount' => count($candidates),
            'alphaNote' => 'Alpha 0.1.7: serverseitige Variantenstamm-Freigabe aktiv. Exakte Nettobestandsmenge wird nach erfolgreichem Frontend-Test ergänzt.'
        ];
    }

    private function parseVariationNumber(string $number): ?array
    {
        if (!preg_match('/^(.+)_([0-9]{2,6})$/', trim($number), $matches)) {
            return null;
        }

        return [
            'stem' => $matches[1],
            'length' => (int) $matches[2]
        ];
    }

    private function isItemEnabled(int $itemId): bool
    {
        $value = (string) $this->config->get('BlechProfilLengthConfigurator.length.enabledItemIds', '260');
        $ids = array_filter(array_map('trim', explode(',', $value)));

        foreach ($ids as $id) {
            if ((int) $id === $itemId) {
                return true;
            }
        }

        return false;
    }

    private function isStemEnabled(string $stem): bool
    {
        $value = (string) $this->config->get('BlechProfilLengthConfigurator.length.enabledPrefixes', 'ERD0204305');
        $prefixes = array_filter(array_map('trim', explode(',', $value)));

        foreach ($prefixes as $prefix) {
            if ($prefix !== '' && $stem === $prefix) {
                return true;
            }
        }

        return false;
    }

    private function error(string $message): array
    {
        return [
            'success' => false,
            'message' => $message
        ];
    }
}
