<?php

namespace BlechProfilLengthConfigurator\Controllers;

use IO\Services\BasketService;
use Plenty\Plugin\Controller;
use Plenty\Plugin\Http\Request;
use Plenty\Plugin\Http\Response;
use BlechProfilLengthConfigurator\Services\LengthResolverService;
use BlechProfilLengthConfigurator\Services\VariationEligibilityService;

class LengthConfiguratorController extends Controller
{
    /** @var Request */
    private $request;

    /** @var Response */
    private $response;

    /** @var LengthResolverService */
    private $resolver;

    /** @var BasketService */
    private $basketService;

    /** @var VariationEligibilityService */
    private $eligibility;

    public function __construct(
        Request $request,
        Response $response,
        LengthResolverService $resolver,
        BasketService $basketService,
        VariationEligibilityService $eligibility
    ) {
        $this->request = $request;
        $this->response = $response;
        $this->resolver = $resolver;
        $this->basketService = $basketService;
        $this->eligibility = $eligibility;
    }

    public function enabled(): Response
    {
        $variationId = (int) $this->request->get('variationId', 0);
        return $this->json($this->eligibility->check($variationId), 200);
    }

    public function resolve(): Response
    {
        $itemId = (int) $this->request->get('itemId', 0);
        $variationId = (int) $this->request->get('variationId', 0);
        $length = (int) $this->request->get('length', 0);
        $quantity = (float) $this->request->get('quantity', 1);

        $result = $this->resolver->resolve($itemId, $variationId, $length, $quantity);
        return $this->json($result, $result['success'] ? 200 : 422);
    }

    public function addToBasket(): Response
    {
        $input = $this->request->all();
        $itemId = isset($input['itemId']) ? (int) $input['itemId'] : 0;
        $variationId = isset($input['variationId']) ? (int) $input['variationId'] : 0;
        $length = isset($input['length']) ? (int) $input['length'] : 0;
        $quantity = isset($input['quantity']) ? (float) $input['quantity'] : 1.0;

        $result = $this->resolver->resolve($itemId, $variationId, $length, $quantity);
        if (!$result['success']) {
            return $this->json($result, 422);
        }

        try {
            $basketItem = $this->basketService->addBasketItem([
                'variationId' => (int) $result['selected']['variationId'],
                'quantity' => $quantity,
                'inputLength' => $length
            ]);

            return $this->json([
                'success' => true,
                'message' => 'Wunschlänge wurde in den Warenkorb gelegt.',
                'selection' => $result,
                'basketItem' => $basketItem
            ]);
        } catch (\Throwable $e) {
            return $this->json([
                'success' => false,
                'message' => 'Die passende Rohmaterial-Variante wurde gefunden, konnte aber nicht in den Warenkorb gelegt werden.',
                'technicalMessage' => $e->getMessage(),
                'selection' => $result
            ], 500);
        }
    }

    private function json(array $payload, int $status = 200): Response
    {
        return $this->response->make(
            json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            $status,
            ['Content-Type' => 'application/json; charset=utf-8']
        );
    }
}
