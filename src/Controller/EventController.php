<?php
// src/Controller/SecurityController.php
namespace App\Controller;

use App\Services\FetchService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


#[Route('/event', name: 'api_events_')]
#[AsController]
class EventController extends AbstractController
{
    public function __construct(
        private FetchService $fetchService
    )
    {
    }

    #[Route("/get-event-details", name: "get_event_details", methods: ["GET"])]
    public function getNextMatchesFromLeague(Request $request
    ): JsonResponse
    {
        $id = $request->get("leagueEventId", 0);

        $event = $this->fetchService->fetch('GET', '/getEventDetails', ['query' => [
            'hl' => 'fr-FR',
            'id' => $id,
        ]]);

        return new JsonResponse([
            'event' => json_decode($event),
        ]);
    }

}