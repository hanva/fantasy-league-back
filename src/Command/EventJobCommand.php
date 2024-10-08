<?php

namespace App\Command;

use App\Entity\Event;
use App\Repository\LeagueRepository;
use App\Services\FetchService;
use Cassandra\Date;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[AsCommand(
    name: 'app:event-job-command',
    description: 'Add a short description for your command',
)]
class EventJobCommand extends Command
{


    public function __construct(
        private ParameterBagInterface  $parameterBag,
        private HttpClientInterface    $httpClient,
        private LeagueRepository       $leagueRepository,
        private EntityManagerInterface $manager,
        private FetchService           $fetchService,

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
        $nextMatches = array_filter($scheduleDecoded->data->schedule->events, function ($match) {
            return $match->state === 'unstarted' || $match->state === 'inProgress';
        });
        $scheduleDecoded->data->schedule->events = array_values($nextMatches);
        foreach ($scheduleDecoded->data->schedule->events as $match) {
            $league = $this->leagueRepository->findOneBy(['slug' => $match->league->slug]);
            if ($league) {
                $events = $league->getEvents();
                foreach ($events as $event) {
                    $leagueEventId = $event->getLeagueEventId();
                    if ($event->getLeagueEventId() === $match->match->id) {
                        $event->setStatus($match->state);
                        $this->manager->persist($event);
                        $this->manager->flush();
                        continue 2;
                    }
                }
                $event = new Event();
                $event->setLeagueEventId($match->match->id);
                $event->setStatus($match->state);
                $datetime = new DateTime($match->startTime);
                $event->setStartDate($datetime);
                $league->addEvent($event);
                $this->manager->persist($league);
                $this->manager->persist($event);
            }
        }
        $this->manager->flush();

        return Command::SUCCESS;
    }
}
