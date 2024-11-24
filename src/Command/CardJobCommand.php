<?php

namespace App\Command;

use App\Entity\Card;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;

#[AsCommand(
    name: 'app:card-job-command',
    description: 'Add all cards to DB',
)]
class CardJobCommand extends Command
{


    protected static $defaultName = 'app:import-cards';
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Imports cards from a JSON file.')
            ->setHelp('This command allows you to import card data into the database from a specified JSON file.')
            ->addArgument('file', InputArgument::REQUIRED, 'The path to the JSON file to import');

    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // JSON data

        $filePath = $input->getArgument('file');
        if (!file_exists($filePath)) {
            $output->writeln("<error>File not found: $filePath</error>");
            return Command::FAILURE;
        }

        // Lire le contenu du fichier
        $json = file_get_contents($filePath);

        $data = json_decode($json, true);

        if ($data === null) {
            $output->writeln('<error>Invalid JSON data</error>');
            return Command::FAILURE;
        }

        foreach ($data as $item) {
            $existingCard = $this->entityManager
                ->getRepository(Card::class)
                ->findOneBy(['name' => $item['name']]);

            if ($existingCard) {
                $output->writeln("<comment>Card already exists: {$item['name']}</comment>");
                continue; // Passer à l'élément suivant
            }
            $card = new Card();
            $card->setName($item['name']);
            $card->setRarity($item['rarity']);
            $card->setDescription($item['description']);

            $this->entityManager->persist($card);
        }

        $this->entityManager->flush();

        $output->writeln('<info>Cards have been successfully imported!</info>');

        return Command::SUCCESS;
    }
}
