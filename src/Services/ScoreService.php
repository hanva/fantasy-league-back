<?php

namespace App\Services;

use App\Entity\Bet;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class ScoreService
{


    public function __construct(
        private ParameterBagInterface $parameterBag,
        private HttpClientInterface   $httpClient,
    )
    {
    }

    public function getFinalScore(Bet $bet, $match): int
    {
        $score = $this->isBetWon($bet, $match) ? +50 : -50;
        if ($score > 0) {
            $lossTeam = $this->getWonTeam($match, true);
            $score += $this->isPerfectScore($bet, $lossTeam[array_key_first($lossTeam)]) ? +50 : 0;
        }
        return $score;
    }

    private function isBetWon(Bet $bet, $match): bool
    {
        return $bet->getTeam() === (array_key_first($this->getWonTeam($match)));
    }

    private function getWonTeam($match, $lose = false): array
    {
        return array_filter($match->teams, function ($team) use ($lose) {
            return $lose === false ? $team->result->outcome === "win" : $team->result->outcome === "loss";
        });
    }


    private function isPerfectScore(Bet $bet, $lossTeam): bool
    {
        return $bet->getGameLooses() === $lossTeam->result->gameWins;
    }
}