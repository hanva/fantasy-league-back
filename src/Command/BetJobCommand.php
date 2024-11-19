<?php

namespace App\Command;

use App\Repository\BetRepository;
use App\Repository\EventRepository;
use App\Services\FetchService;
use App\Services\ScoreService;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[AsCommand(
    name: 'app:bet-job-command',
    description: 'Add a short description for your command',
)]
class BetJobCommand extends Command
{


    public function __construct(
        private ParameterBagInterface  $parameterBag,
        private HttpClientInterface    $httpClient,
        private EventRepository        $eventRepository,
        private FetchService           $fetchService,
        private BetRepository          $betRepository,
        private ScoreService           $scoreService,
        private EntityManagerInterface $manager,
    )
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('arg1', InputArgument::OPTIONAL, 'Argument description')
            ->addOption('option1', null, InputOption::VALUE_NONE, 'Option description');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {

        $id = implode(',', $this->fetchService->selectedLeagues);

        $schedule = $this->fetchService->fetch('GET', '/getSchedule', ['query' => [
            'hl' => 'fr-FR',
            'leagueId' => $id,
        ]]);

        $scheduleDecoded = json_decode($schedule);

        $completedMatches = array_filter($scheduleDecoded->data->schedule->events, function ($match) {
            return $match->state === 'completed';
        });

        $events = $this->eventRepository->findBy(['status' => ['unstarted', 'inProgress']]);

        foreach ($events as $event) {
            foreach ($completedMatches as $match) {
                if ($event->getLeagueEventId() == $match->match->id) {
                    $matchDetails = $this->fetchService->fetch('GET', '/getEventDetails', ['query' => [
                        'id' => $match->match->id,
                        'hl' => 'fr-FR',
                    ]]);
                    $matchDetailsDecoded = json_decode($matchDetails);
                    $games = array_map(function ($item) {
                        return $item->id ?? null; // Retourne l'ID si présent, sinon null
                    }, $matchDetailsDecoded->data->event->match->games);
                    foreach ($games as $id) {
                        $gameData = $this->fetchService->fetch('GET', '/window/' . $id, [
                        ], $this->parameterBag->get('api_live_url'));
                        //Getting first frame of the game; but if its working right we could just use date of now + 1
                        $gameDataDecoded = json_decode($gameData);
                        $time = $gameDataDecoded->frames[0]->rfc460Timestamp;
                        $date = new DateTime($time);
                        $date->modify('+1 day');
                        $date->setTime(0, 0, 0);

                        $gameData = $this->fetchService->fetch('GET', '/window/' . $id, [
                            'query' => [
                                'startingTime' => $date->format('Y-m-d\TH:i:s\Z')
                            ]
                        ], $this->parameterBag->get('api_live_url'));
                        $fullGameData = json_decode($gameData);
                        dump(end($fullGameData->frames));
                        die;
                    }

//                    $bets = $this->betRepository->findBy(['leagueEventId' => $event->getLeagueEventId()]);
//                    foreach ($bets as $bet) {
//                        $score = $this->scoreService->getFinalScore($bet, $match->match);
//                        $bet->setScore($score);
//                        $this->manager->persist($bet);
//                    }
//                    $event->setStatus('completed');
//                    $this->manager->persist($event);
                }
            }
        }
        $this->manager->flush();

        return Command::SUCCESS;
    }
}
