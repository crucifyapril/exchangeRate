<?php

namespace App\Service;

use App\Entity\CurrencyRate;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Cache\InvalidArgumentException;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

readonly class CurrencyService
{
    public function __construct(
        private HttpClientInterface $client,
        private EntityManagerInterface $entityManager,
        private CacheItemPoolInterface $cache
    ) {
    }

    /**
     * @throws TransportExceptionInterface
     * @throws InvalidArgumentException
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ClientExceptionInterface
     * @throws \Exception
     */
    public function updateCurrencyRates(): void
    {
        $response = $this->client->request('GET', 'http://www.cbr.ru/scripts/XML_daily.asp');
        $xmlContent = $response->getContent();

        $xml = simplexml_load_string($xmlContent);
        if (!$xml) {
            throw new \Exception('Не удалось загрузить XML-данные с ЦБ РФ');
        }

        foreach ($xml->Valute as $valute) {
            $currencyCode = (string) $valute->CharCode;
            $rate = (float) str_replace(',', '.', $valute->Value);
            $nominal = (int) $valute->Nominal;

            $newRate = $rate / $nominal;

            $date = new DateTime((string) $xml['Date']);

            $repository = $this->entityManager->getRepository(CurrencyRate::class);
            $existingRate = $repository->findOneBy(['currencyCode' => $currencyCode], ['date' => 'DESC']);

            // Определить тренд
            $trend = null;
            if ($existingRate) {
                $oldRate = (float) $existingRate->getRate();
                if ($newRate > $oldRate) {
                    $trend = 'up';
                } elseif ($newRate < $oldRate) {
                    $trend = 'down';
                }
            }

            if (!$existingRate || $existingRate->getDate()->format('Y-m-d') !== $date->format('Y-m-d')) {
                $currencyRate = new CurrencyRate();
                $currencyRate->setCurrencyCode($currencyCode)
                    ->setRate($newRate)
                    ->setDate($date)
                    ->setTrend($trend);

                $this->entityManager->persist($currencyRate);
            } else {
                $existingRate->setRate($newRate)
                    ->setTrend($trend)
                    ->setDate($date);
                $this->entityManager->persist($existingRate);
            }
        }

        $this->entityManager->flush();

        $this->cache->delete('currency_rates');
    }

    public function getLatestRates(): array
    {
        $cachedRates = $this->cache->getItem('currency_rates');

        if (!$cachedRates->isHit()) {
            $currencyRates = $this->entityManager->getRepository(CurrencyRate::class)
                ->findBy([], ['date' => 'DESC']);

            $rates = [];
            foreach ($currencyRates as $rate) {
                $rates[] = [
                    'currencyCode' => $rate->getCurrencyCode(),
                    'rate' => $rate->getRate(),
                    'date' => $rate->getDate()->format('Y-m-d H:i:s'),
                    'trend' => $rate->getTrend(),
                ];
            }

            // Сохраняем данные в кэш
            $cachedRates->set($rates);
            $cachedRates->expiresAfter(600);
            $this->cache->save($cachedRates);
        }

        return $cachedRates->get();
    }

    // Метод для сброса кэша
    public function clearCache(): void
    {
        $this->cache->delete('currency_rates');
    }
}
