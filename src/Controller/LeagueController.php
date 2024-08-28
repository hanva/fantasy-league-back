<?php
// src/Controller/SecurityController.php
namespace App\Controller;

use ApiPlatform\Metadata\ApiResource;
use App\Service\Secta\SectaContentService;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
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
    private $selectedLeagues = [
        'LEC' => '98767991302996019',
        'LCS' => '98767991299243165',
        'LCK' => '98767991310872058',
        'LPL' => '98767991314006698',
    ];

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
        $leagues = $this->fetch('GET', '/getLeagues', ['query' => [
            'hl' => 'fr-FR',
        ]]);
        return new JsonResponse([
            'leagues' => json_decode($leagues),
        ]);
    }

    #[Route("/{id}", name: "get_league_schedule", methods: ["GET"],)]
    public function getLeagueSchedule(
        string  $id,
        Request $request
    ): JsonResponse
    {
        $onlyNextMatches = $request->get("onlyNextMatches", 0);

        // TO DO Move all results and map in entity so $scheduleDecoded->data->schedule->events is defined -> interface|entity

        if ($id === 'selected') {
            $id = implode(',', $this->selectedLeagues);
        }

        $schedule = $this->fetch('GET', '/getSchedule', ['query' => [
            'hl' => 'fr-FR',
            'leagueId' => $id,
        ]]);

        $scheduleDecoded = json_decode($schedule);

        if ($onlyNextMatches) {
            $nextMatches = array_filter($scheduleDecoded->data->schedule->events, function ($match) {
                return $match->state === 'unstarted';
            });
            $scheduleDecoded->data->schedule->events = array_values($nextMatches);
        }

        return new JsonResponse([
            'leagues' => $scheduleDecoded,
        ]);
    }
}