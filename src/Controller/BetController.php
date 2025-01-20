<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class BetController extends AbstractController
{
    #[Route('/bet', name: 'app_bet')]
    public function index(): Response
    {
        return $this->render('bet/index.html.twig', [
            'controller_name' => 'BetController',
        ]);
    }
    
    #[Route("/{id}/update", name: "update", methods: ["PUT"])]
    public function updateBet(
        int $id,
        Request $request,
        EntityManagerInterface $entityManager
    ): JsonResponse {
        $betRepository = $entityManager->getRepository(Bet::class);
        $cardRepository = $entityManager->getRepository(Card::class);

        // Fetch the bet by ID
        $bet = $betRepository->find($id);
        if (!$bet) {
            return new JsonResponse(["error" => "Bet not found"], 404);
        }

        $data = json_decode($request->getContent(), true);
        if (!$data) {
            return new JsonResponse(["error" => "Invalid JSON"], 400);
        }

        // Check if we need to update the bet's properties
        if (isset($data['team'])) {
            $bet->setTeam($data['team']);
        }
        if (isset($data['gameWins'])) {
            $bet->setGameWins($data['gameWins']);
        }
        if (isset($data['gameLooses'])) {
            $bet->setGameLooses($data['gameLooses']);
        }

        // If cards are provided, add them to the bet
        if (isset($data['cards']) && is_array($data['cards'])) {
            foreach ($data['cards'] as $cardId) {
                $card = $cardRepository->find($cardId);
                if ($card) {
                    $bet->addCard($card);
                }
            }
        }

        // Save the changes
        $entityManager->persist($bet);
        $entityManager->flush();

        return new JsonResponse(["message" => "Bet updated successfully"]);
    }
}
