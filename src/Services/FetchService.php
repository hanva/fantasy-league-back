<?php

namespace App\Services;

use App\Repository\LeagueRepository;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class FetchService
{
    public $selectedLeagues = [
        'LEC' => '98767991302996019',
        'LCS' => '98767991299243165',
        'LCK' => '98767991310872058',
        'LPL' => '98767991314006698',
        'Mondial' => '98767975604431411',
        'Worlds Qualifying Series' => '110988878756156222',
    ];

    public function __construct(
        private ParameterBagInterface $parameterBag,
        private HttpClientInterface   $httpClient,
    )
    {
    }

    public function fetch(
        string $method,
        string $url,
        array  $options = [],
        string $baseUrl = null
    )
    {
        if (!$baseUrl) $baseUrl = $this->parameterBag->get('api_url');
        if (!(isset($options['headers']))) {
            $options['headers'] = [
                'x-api-key' => $this->parameterBag->get('api_key')
            ];

        }

        $destination = "{$baseUrl}{$url}";

        $response = $this->httpClient->request(
            $method,
            $destination,
            $options
        );
        return $response->getContent();
    }
}