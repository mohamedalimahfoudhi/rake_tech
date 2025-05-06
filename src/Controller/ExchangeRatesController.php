<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class ExchangeRatesController extends AbstractController
{
    private $httpClient;
    private $apiKey = 'c5992377cb356ff6b37352d57e151ee3';
    private $baseUrl = 'https://api.exchangeratesapi.io/v1/';

    public function __construct(HttpClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    #[Route('/exchange-rates', name: 'exchange_rates')]
    public function index(): Response
    {
        // Get the latest exchange rates
        $latestRates = $this->getLatestRates();
        
        return $this->render('exchange_rates/index.html.twig', [
            'latest_rates' => $latestRates
        ]);
    }

    #[Route('/exchange-rates/convert', name: 'exchange_rates_convert')]
    public function convert(Request $request): Response
    {
        $from = $request->query->get('from', 'EUR');
        $to = $request->query->get('to', 'USD');
        $amount = (float)$request->query->get('amount', 1);
        
        // Get latest rates for conversion
        $latestRates = $this->getLatestRates();
        
        // Perform conversion manually since the API's convert endpoint requires a paid subscription
        $conversion = [];
        
        if (isset($latestRates['error'])) {
            $conversion['error'] = $latestRates['error'];
        } else {
            $conversion['success'] = true;
            $conversion['date'] = $latestRates['date'] ?? date('Y-m-d');
            $conversion['timestamp'] = $latestRates['timestamp'] ?? time();
            
            // Get rates from latest rates response
            $rates = $latestRates['rates'] ?? [];
            $baseRate = 1; // EUR is base by default
            
            // If FROM is not EUR, we need to find its rate first
            if ($from !== 'EUR' && isset($rates[$from])) {
                $baseRate = $rates[$from];
            }
            
            // Calculate the result
            if ($to === 'EUR') {
                // Convert to EUR (base currency)
                $conversion['result'] = $amount / $baseRate;
            } elseif (isset($rates[$to])) {
                // Convert to target currency
                $conversion['result'] = ($amount / $baseRate) * $rates[$to];
            } else {
                $conversion = ['error' => "Currency $to not found in available rates"];
            }
        }
        
        // Get available symbols for dropdown
        $symbols = $this->getSupportedSymbols();
        
        return $this->render('exchange_rates/convert.html.twig', [
            'from' => $from,
            'to' => $to,
            'amount' => $amount,
            'conversion' => $conversion,
            'symbols' => $symbols
        ]);
    }
    
    #[Route('/exchange-rates/historical', name: 'exchange_rates_historical')]
    public function historical(Request $request): Response
    {
        $date = $request->query->get('date', date('Y-m-d', strtotime('-1 day')));
        $symbols = $request->query->get('symbols', 'USD,EUR,GBP,JPY');
        
        // Get historical rates
        $historicalRates = $this->getHistoricalRates($date, $symbols);
        
        return $this->render('exchange_rates/historical.html.twig', [
            'date' => $date,
            'symbols' => $symbols,
            'historical_rates' => $historicalRates
        ]);
    }
    
    #[Route('/exchange-rates/timeseries', name: 'exchange_rates_timeseries')]
    public function timeSeries(Request $request): Response
    {
        $startDate = $request->query->get('start_date', date('Y-m-d', strtotime('-7 days')));
        $endDate = $request->query->get('end_date', date('Y-m-d'));
        $symbols = $request->query->get('symbols', 'USD,EUR,GBP,JPY');
        
        // Get time series data
        $timeSeriesData = $this->getTimeSeriesData($startDate, $endDate, $symbols);
        
        return $this->render('exchange_rates/timeseries.html.twig', [
            'start_date' => $startDate,
            'end_date' => $endDate,
            'symbols' => $symbols,
            'timeseries_data' => $timeSeriesData
        ]);
    }
    
    private function getLatestRates(string $symbols = ''): array
    {
        try {
            $endpoint = 'latest';
            $url = $this->baseUrl . $endpoint . '?access_key=' . $this->apiKey;
            
            if (!empty($symbols)) {
                $url .= '&symbols=' . $symbols;
            }
            
            $response = $this->httpClient->request('GET', $url);
            $data = $response->toArray();
            
            if (!isset($data['success']) || $data['success'] === false) {
                return ['error' => $data['error']['info'] ?? 'Unknown error'];
            }
            
            return $data;
        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }
    
    private function getSupportedSymbols(): array
    {
        // Since the symbols endpoint might also require a paid subscription,
        // we'll provide a basic list of common currencies
        return [
            'EUR' => 'Euro',
            'USD' => 'US Dollar',
            'GBP' => 'British Pound',
            'JPY' => 'Japanese Yen',
            'AUD' => 'Australian Dollar',
            'CAD' => 'Canadian Dollar',
            'CHF' => 'Swiss Franc',
            'CNY' => 'Chinese Yuan',
            'INR' => 'Indian Rupee',
            'MXN' => 'Mexican Peso',
            'RUB' => 'Russian Ruble',
            'ZAR' => 'South African Rand'
        ];
    }
    
    private function getHistoricalRates(string $date, string $symbols = ''): array
    {
        try {
            $url = $this->baseUrl . $date . '?access_key=' . $this->apiKey;
            
            if (!empty($symbols)) {
                $url .= '&symbols=' . $symbols;
            }
            
            $response = $this->httpClient->request('GET', $url);
            $data = $response->toArray();
            
            if (!isset($data['success']) || $data['success'] === false) {
                return ['error' => $data['error']['info'] ?? 'Unknown error'];
            }
            
            return $data;
        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }
    
    private function getTimeSeriesData(string $startDate, string $endDate, string $symbols = ''): array
    {
        try {
            $endpoint = 'timeseries';
            $url = $this->baseUrl . $endpoint . '?access_key=' . $this->apiKey . '&start_date=' . $startDate . '&end_date=' . $endDate;
            
            if (!empty($symbols)) {
                $url .= '&symbols=' . $symbols;
            }
            
            $response = $this->httpClient->request('GET', $url);
            $data = $response->toArray();
            
            if (!isset($data['success']) || $data['success'] === false) {
                return ['error' => $data['error']['info'] ?? 'Unknown error'];
            }
            
            return $data;
        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }
} 