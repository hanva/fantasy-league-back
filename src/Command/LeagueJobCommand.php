<?php

namespace App\Command;

use App\Entity\League;
use App\Repository\LeagueRepository;
use App\Services\FetchService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:league-job-command',
    description: 'Add a short description for your command',
)]
class LeagueJobCommand extends Command
{

    public function __construct(
        private LeagueRepository       $leagueRepository,
        private FetchService           $fetchService,
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

        $leagues = $this->fetchService->fetch('GET', '/getLeagues', ['query' => [
            'hl' => 'fr-FR',
        ]]);
        $leaguesDecoded = json_decode($leagues);

        foreach ($leaguesDecoded->data->leagues as $league) {
            $leagueItem = $this->leagueRepository->findBy(['leagueId' => $league->id]);
            if (!$leagueItem && in_array($league->id, array_values($this->fetchService->selectedLeagues))) {
                $fantasyLeagueItem = new League();
                $fantasyLeagueItem->setLeagueId($league->id);
                $fantasyLeagueItem->setName($league->name);
                $fantasyLeagueItem->setSlug($league->slug);
                $this->manager->persist($fantasyLeagueItem);
                $this->manager->flush();
            }
        }
        return Command::SUCCESS;
    }
}
