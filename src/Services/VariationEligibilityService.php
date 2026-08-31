<?php

namespace BlechProfilLengthConfigurator\Services;

use Plenty\Modules\Item\Variation\Contracts\VariationRepositoryContract;
use Plenty\Modules\Pim\SearchService\Filter\PropertyFilter;
use Plenty\Modules\Pim\SearchService\Filter\VariationBaseFilter;
use Plenty\Modules\Pim\VariationDataInterface\Contracts\VariationDataInterfaceContract;
use Plenty\Modules\Pim\VariationDataInterface\Model\VariationDataInterfaceContext;
use Plenty\Modules\Property\V2\Contracts\PropertySelectionRepositoryContract;
use Plenty\Plugin\ConfigRepository;

class VariationEligibilityService
{
    /** @var VariationDataInterfaceContract */
    private $variationDataInterface;

    /** @var PropertySelectionRepositoryContract */
    private $selectionRepository;

    /** @var VariationRepositoryContract */
    private $variationRepository;

    /** @var ConfigRepository */
    private $config;

    public function __construct(
        VariationDataInterfaceContract $variationDataInterface,
        PropertySelectionRepositoryContract $selectionRepository,
        VariationRepositoryContract $variationRepository,
        ConfigRepository $config
    ) {
        $this->variationDataInterface = $variationDataInterface;
        $this->selectionRepository = $selectionRepository;
        $this->variationRepository = $variationRepository;
        $this->config = $config;
    }

    public function check(int $variationId): array
    {
        $propertyId = (int) $this->config->get(
            'BlechProfilLengthConfigurator.length.activationPropertyId',
            48
        );
        $selectionId = (int) $this->config->get(
            'BlechProfilLengthConfigurator.length.activationSelectionId',
            71
        );

        if ($variationId <= 0) {
            return $this->result(false, 'Ungültige Varianten-ID.', $variationId, $propertyId, $selectionId, '', 'validation');
        }

        if ($propertyId <= 0 || $selectionId <= 0) {
            return $this->result(false, 'Eigenschafts-ID oder Ja-Auswahl-ID ist im Plugin nicht korrekt hinterlegt.', $variationId, $propertyId, $selectionId, '', 'config');
        }

        $variationNumber = '';

        try {
            $variation = $this->variationRepository->findById($variationId);
            if ($variation) {
                $variationNumber = (string) $variation->number;
            }
        } catch (\Throwable $e) {
            return $this->result(false, 'Die Variante konnte nicht geladen werden: ' . $e->getMessage(), $variationId, $propertyId, $selectionId, '', 'variation');
        }

        // Sicherstellen, dass die konfigurierte Auswahl-ID wirklich zur konfigurierten
        // Eigenschaft gehört. Bei einer Auswahl-Eigenschaft ist die Auswahl-ID systemweit
        // eindeutig und kann danach direkt als PIM-Filter verwendet werden.
        try {
            $selection = $this->selectionRepository->get($selectionId);
            if (!$selection || (int) $selection->propertyId !== $propertyId) {
                $actualPropertyId = $selection ? (int) $selection->propertyId : 0;
                return $this->result(
                    false,
                    'Die Auswahl-ID ' . $selectionId . ' gehört nicht zur Eigenschaft ' . $propertyId . '. Tatsächliche Eigenschaft: ' . $actualPropertyId . '.',
                    $variationId,
                    $propertyId,
                    $selectionId,
                    $variationNumber,
                    'selection-config'
                );
            }
        } catch (\Throwable $e) {
            return $this->result(false, 'Die Auswahl-Konfiguration konnte nicht geprüft werden: ' . $e->getMessage(), $variationId, $propertyId, $selectionId, $variationNumber, 'selection-load');
        }

        try {
            /** @var VariationDataInterfaceContext $context */
            $context = pluginApp(VariationDataInterfaceContext::class);

            /** @var VariationBaseFilter $variationFilter */
            $variationFilter = pluginApp(VariationBaseFilter::class);
            $variationFilter->hasId($variationId);

            /** @var PropertyFilter $propertyFilter */
            $propertyFilter = pluginApp(PropertyFilter::class);
            $propertyFilter->hasPropertySelection($selectionId);

            $context->addFilter($variationFilter);
            $context->addFilter($propertyFilter);
            $context->setPage(1, 1);

            $result = $this->variationDataInterface->getResult($context);
            $matches = (int) $result->total();

            if ($matches > 0) {
                return $this->result(true, 'Eigenschaft ' . $propertyId . ' mit Auswahl ' . $selectionId . ' ist an dieser Variante verknüpft.', $variationId, $propertyId, $selectionId, $variationNumber, 'pim-filter');
            }

            return $this->result(false, 'Die Variante besitzt die Ja-Auswahl ' . $selectionId . ' nicht.', $variationId, $propertyId, $selectionId, $variationNumber, 'pim-filter');
        } catch (\Throwable $e) {
            return $this->result(false, 'PIM-Prüfung fehlgeschlagen: ' . $e->getMessage(), $variationId, $propertyId, $selectionId, $variationNumber, 'pim-error');
        }
    }

    private function result(
        bool $enabled,
        string $message,
        int $variationId,
        int $propertyId,
        int $selectionId,
        string $variationNumber,
        string $source
    ): array {
        return [
            'success' => true,
            'enabled' => $enabled,
            'message' => $message,
            'variationId' => $variationId,
            'variationNumber' => $variationNumber,
            'propertyId' => $propertyId,
            'selectionId' => $selectionId,
            'source' => $source
        ];
    }
}
