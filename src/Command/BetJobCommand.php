<?php

namespace App\Command;

use App\Repository\BetRepository;
use App\Repository\EventRepository;
use App\Services\FetchService;
use App\Services\ScoreService;
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
                    $matchDetails = $this->fetchService->fetch('GET', '/' . 112449598160932436, ['query' => [
//                        'gameId' => $match->match->id,
                    ]], $this->parameterBag->get('api_live_url'));
//                    dump($completedMatches);
                    dump($matchDetails);
                    die;
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
