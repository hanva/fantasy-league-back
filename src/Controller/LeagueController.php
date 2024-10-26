<?php
// src/Controller/SecurityController.php
namespace App\Controller;

use ApiPlatform\Metadata\ApiResource;
use App\Services\FetchService;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[Route('/league', name: 'api_leagues_')]
#[AsController]
class LeagueController extends AbstractController
{
    public function __construct(
        private FetchService $fetchService
    )
    {
    }

    #[Route("/get-leagues", name: "get_leagues", methods: ["GET"])]
    public function getNextMatchesFromLeague(): JsonResponse
    {
        $leagues = $this->fetchService->fetch('GET', '/getLeagues', ['query' => [
            'hl' => 'fr-FR',
        ]]);
        return new JsonResponse([
            'leagues' => json_decode($leagues),
        ]);
    }

    #[Route("/{id}", name: "get_league_schedule", methods: ["GET"])]
    public function getLeagueSchedule(
        string  $id,
        Request $request
    ): JsonResponse
    {
        $onlyNextMatches = $request->get("onlyNextMatches", 0);

        // TO DO Move all results and map in entity so $scheduleDecoded->data->schedule->events is defined -> interface|entity
        if ($id === 'selected') {
            $id = implode(',', $this->fetchService->selectedLeagues);
        }

        $schedule = $this->fetchService->fetch('GET', '/getSchedule', ['query' => [
            'hl' => 'fr-FR',
            'leagueId' => $id,
        ]]);

        $scheduleDecoded = json_decode($schedule);

        $user = $this->getUser();

        if ($onlyNextMatches) {
            if ($user) {
                $bets = $user->getBets();
                $leagueEventIds = $bets->map(fn($bet) => $bet->getLeagueEventId())->toArray();
                $nextMatches = array_filter($scheduleDecoded->data->schedule->events, function ($match) use ($leagueEventIds) {
                    return $match->state === 'unstarted' ||
                        $match->state === 'inProgress' || in_array($match->match->id, $leagueEventIds);
                });
                $nextMatches = array_values($nextMatches);
            } else {
                $nextMatches = array_filter($scheduleDecoded->data->schedule->events, function ($match) {
                    return $match->state === 'unstarted' || $match->state === 'inProgress';
                });
            }
            $scheduleDecoded->data->schedule->events = array_values($nextMatches);
        }

        return new JsonResponse([
            'leagues' => $scheduleDecoded,
        ]);
    }
}