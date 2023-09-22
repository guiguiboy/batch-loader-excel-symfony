<?php

namespace App\Command;

use App\Entity\LineFile;
use App\Service\LineFileService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:batch-processing',
    description: 'Add a short description for your command',
)]
class BatchProcessingCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly LineFileService $lineFileService
    )
    {
        parent::__construct();
    }


    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $this->em->getConnection()->getConfiguration()->setMiddlewares([]);
        $this->em->getRepository(LineFile::class)->removeAll();


        $iterator = $this->lineFileService->getIterator();
        $i = 0;

        foreach ($iterator as $l) {
            $lineFile = new LineFile();
            $lineFile->setCode($l['code']);
            $lineFile->setDate($l['date']);
            $lineFile->setType($l['type']);
            $lineFile->setValue($l['value']);
            $lineFile->setStatus($l['status']);

            $this->em->persist($lineFile);
            $i++;
            if ($i % 100 === 0) {
                $this->em->flush();
                $this->logMemory($output, $i);
                $this->em->clear();
            }
        }

        return Command::SUCCESS;
    }

    private function logMemory(OutputInterface $output, $log)
    {
        $memory = memory_get_usage(true) / 1024 / 1024;
        $peakMemory = memory_get_peak_usage(true) / 1024 / 1024;
        $output->writeln('Memory consumption ' . implode(', ', [
            $log, 'mem: ' . $memory . 'MB', 'peak: ' . $peakMemory . 'MB',
        ]));
    }
}
