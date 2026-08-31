<?php

namespace BlechProfilLengthConfigurator\Services;

use IO\Services\ItemService;
use Plenty\Modules\Item\Variation\Contracts\VariationRepositoryContract;
use Plenty\Modules\Item\VariationStock\Contracts\VariationStockRepositoryContract;
use Plenty\Plugin\ConfigRepository;

class LengthResolverService
{
    /** @var ItemService */
    private $itemService;

    /** @var VariationRepositoryContract */
    private $variationRepository;

    /** @var VariationStockRepositoryContract */
    private $stockRepository;

    /** @var ConfigRepository */
    private $config;

    public function __construct(
        ItemService $itemService,
        VariationRepositoryContract $variationRepository,
        VariationStockRepositoryContract $stockRepository,
        ConfigRepository $config
    ) {
        $this->itemService = $itemService;
        $this->variationRepository = $variationRepository;
        $this->stockRepository = $stockRepository;
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

        $variationIds = $this->itemService->getVariationIds($itemId);

        if (!is_array($variationIds) || count($variationIds) === 0) {
            return $this->error('Für den Artikel wurden keine Varianten gefunden.');
        }

        $variationIds = array_values(array_unique(array_map('intval', $variationIds)));

        if (!in_array($currentVariationId, $variationIds, true)) {
            return $this->error('Die gewählte Variante gehört nicht zum angegebenen Artikel.');
        }

        $variations = $this->variationRepository->showMultiple($variationIds, []);
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
            $isActive = (bool) $variation->isActive;
            $candidateParsed = $this->parseVariationNumber($number);

            if (!$isActive || $id <= 0 || $candidateParsed === null || $candidateParsed['stem'] !== $stem) {
                continue;
            }

            $rawLength = $candidateParsed['length'];
            if ($rawLength < $requiredPerPiece) {
                continue;
            }

            $netStock = $this->getNetStock($id);
            if ($netStock + 0.00001 < $quantity) {
                continue;
            }

            $candidates[] = [
                'variationId' => $id,
                'variationNumber' => $number,
                'externalId' => (string) $variation->externalId,
                'rawLength' => $rawLength,
                'netStock' => $netStock,
                'restPerPiece' => max(0, $rawLength - $desiredLength - $sawKerf)
            ];
        }

        if (count($candidates) === 0) {
            return $this->error('Keine passende Lagerlänge mit ausreichendem Nettobestand gefunden.');
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
            'alphaNote' => 'Alpha 0.1.3 nutzt Artikel-/Varianten-ID als Primärschlüssel. Mehrteilige Zuschnittoptimierung folgt später.'
        ];
    }

    private function getNetStock(int $variationId): float
    {
        $stockRows = $this->stockRepository->listStockByWarehouse(
            $variationId,
            ['variationId', 'warehouseId', 'netStock'],
            1,
            200
        );

        $sum = 0.0;
        foreach ($stockRows as $row) {
            $sum += (float) $row->netStock;
        }

        return $sum;
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
            if ($prefix !== '' && strpos($stem, $prefix) === 0) {
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
