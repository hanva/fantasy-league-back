<?php
// src/Controller/SecurityController.php
namespace App\Controller;

use ApiPlatform\Metadata\ApiResource;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[Route('/league', name: 'api_league_')]
#[AsController]
class LeagueController extends AbstractController
{
    private string $apiUrl;

    public function __construct(
        private ParameterBagInterface $parameterBag,
        private HttpClientInterface   $httpClient
    )
    {
    }

    public function fetch(
        string $method,
        string $url,
        array  $options = []
    )
    {
        if (!(isset($options['headers']))) {
            $options['headers'] = [
                'x-api-key' => $this->parameterBag->get('api_key')
            ];

            $options['query'] = [
                'hl' => 'fr-FR',
            ];

        }

        $destination = "{$this->parameterBag->get('api_url')}{$url}";

        $response = $this->httpClient->request(
            $method,
            $destination,
            $options
        );
        return $response->getContent();
    }

    #[Route("/get-leagues", name: "get_leagues", methods: ["GET"])]
    public function getNextMatchesFromLeague(): JsonResponse
    {
        $leagues = $this->fetch('GET', '/');
        return new JsonResponse([
            'leagues' => $leagues,
        ]);
    }
}