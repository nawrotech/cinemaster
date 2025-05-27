<?php

namespace App\Command;

use App\Entity\Seat;
use App\Repository\SeatRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Validator\Constraints\LessThan;
use Symfony\Component\Validator\Constraints\Positive;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[AsCommand(
    name: 'app:seed-base-data',
    description: 'Creates basic seats',
)]
class SeedBaseDataCommand extends Command
{
    public function __construct(
        private SeatRepository $seatRepository,
        private ValidatorInterface $validator,
        private EntityManagerInterface $em
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument(
                'gridSize',
                InputArgument::REQUIRED,
                'Grid size (e.g., 10 for a 10x10 grid)'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        if ($this->seatRepository->count() > 0) {
            $io->error('Seats already exist in the database.');
            return Command::FAILURE;
        }

        if (!ctype_digit($input->getArgument('gridSize'))) {
            $io->error('Argument must be an integer.');
            return Command::FAILURE;
        }

        $gridSize = (int) $input->getArgument('gridSize');

        $violations = $this->validator->validate($gridSize, [
            new Type('integer'),
            new Positive(),
            new LessThan(500)
        ]);

        if (count($violations) > 0) {
            foreach ($violations as $violation) {
                $io->error($violation->getMessage());
            }
            return Command::FAILURE;
        }

        $batchSize = 50;
        for ($row = 1; $row <= $gridSize; $row++) {
            for ($seatsInRow = 1; $seatsInRow <= $gridSize; $seatsInRow++) {
                $seat = new Seat();

                $seat->setRowNum($row);
                $seat->setSeatNumInRow($seatsInRow);
                $this->em->persist($seat);

                if (($row * $seatsInRow % $batchSize) === 0) {
                    $this->em->flush();
                    $this->em->clear();
                }
            }
        }
        $this->em->flush();
        $this->em->clear();

        $io->success(sprintf('Successfully created %d seats!', $gridSize * $gridSize));

        return Command::SUCCESS;
    }
}
