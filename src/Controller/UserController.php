<?php

namespace App\Controller;

use App\Entity\Bet;
use App\Repository\CardRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/users', name: 'api_users_')]
class UserController extends AbstractController
{
    private $entityManager;
    private $passwordHasher;

    public function __construct(EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher, private UserRepository $userRepository, private CardRepository $cardRepository)
    {
        $this->entityManager = $entityManager;
        $this->passwordHasher = $passwordHasher;
    }

    #[Route("/register", name: "register", methods: ["POST"])]
    public function register(Request $request, UserRepository $userRepository): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        // Validate input
        if (!isset($data['email']) || !isset($data['password'])) {
            return new JsonResponse(['error' => 'Invalid input'], Response::HTTP_BAD_REQUEST);
        }
        if ($userRepository->findOneBy(['email' => $data['email']])) {
            return new JsonResponse(['code' => 'account-exist'], JsonResponse::HTTP_UNAUTHORIZED);
        }

        $user = new User();
        $user->setEmail($data['email']);
        $hashedPassword = $this->passwordHasher->hashPassword($user, $data['password']);
        $user->setPassword($hashedPassword);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return new JsonResponse(['status' => 'User created!'], JsonResponse::HTTP_CREATED);
    }

    #[Route("/by-email", name: "get_by_email", methods: ["POST"])]
    public function getByEmail(Request $request, SerializerInterface $serializer): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        // Validate input
        if (!isset($data['email'])) {
            return new JsonResponse(['error' => 'Invalid input', 'code' => 'invalid_input'], Response::HTTP_BAD_REQUEST);
        }

        $user = $this->userRepository->findOneBy(['email' => $data['email']]);

        if (!$user) {
            return new JsonResponse(['error' => 'User not found', 'code' => 'user_not_found'], Response::HTTP_BAD_REQUEST);
        }

        if (!$user->getRoles() || !in_array('ROLE_USER', $user->getRoles())) {
            return new JsonResponse(['error' => 'You do not have access to this user', 'code' => 'user_forbidden'], Response::HTTP_BAD_REQUEST);
        }

        return new JsonResponse($user->toArray(), JsonResponse::HTTP_CREATED);
    }

    #[Route("/bets/list", name: "get_user_bets", methods: ["GET"])]
    public function getBets(Request $request): JsonResponse
    {
        $user = $this->getUser();

        if (!$user) {
            return new JsonResponse(['message' => 'User not found.'], 404);
        }

        $bets = $user->getBets();
        // TO DO CHECK IF RETURN ARE NORMALIZED

        return $this->json($bets);

    }

    #[Route("/cards/available_cards", name: "get_user_available_cards", methods: ["GET"])]
    public function getUserAvailableCards(Request $request): JsonResponse
    {
        // TO DO, GET CARDS FROM USER
        $cards = $this->cardRepository->findAll();

        return $this->json($cards);

    }
}