<?php

namespace App\Controller;

use App\Service\CurrencyService;
use Psr\Cache\InvalidArgumentException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class CurrencyController extends AbstractController
{
    public function __construct(
        private readonly CurrencyService $currencyService,
        private readonly CacheInterface $cache
    ) {
    }

    #[Route('/api/currency', name: 'api_currency', methods: ['GET'])]
    public function getCurrencyRates(): JsonResponse
    {
        $currencyRates = $this->cache->get('currency_rates_api', function (ItemInterface $item) {
            $item->expiresAfter(600);

            $this->currencyService->updateCurrencyRates();
            return $this->currencyService->getLatestRates();
        });

        return new JsonResponse($currencyRates);
    }

    /**
     * @throws TransportExceptionInterface
     * @throws InvalidArgumentException
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ClientExceptionInterface
     */
    #[Route('/api/refresh-currency', name: 'api_refresh_currency', methods: ['POST'])]
    public function refreshCurrencyRates(): Response
    {
        $this->cache->delete('currency_rates_api');

        $this->currencyService->updateCurrencyRates();

        return new Response('Курсы валют обновлены и кэш сброшен', Response::HTTP_OK);
    }

    /**
     * @throws InvalidArgumentException
     */
    #[Route('/', name: 'my_page')]
    public function myPage(): Response
    {
        $currencyRates = $this->cache->get('currency_rates_page', function (ItemInterface $item) {
            $item->expiresAfter(600);

            return $this->currencyService->getLatestRates();
        });

        return $this->render('index.html.twig', [
            'title' => 'Моя страница',
            'content' => 'Добро пожаловать на мою страницу!',
            'currencyRates' => $currencyRates,
        ]);
    }
}