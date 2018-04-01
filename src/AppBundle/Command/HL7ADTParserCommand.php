<?php

namespace AppBundle\Command;

use AppBundle\Exception\Factory\FactoryMessageNotSupportedException;
use AppBundle\Factory\DoctorFactoryInterface;
use AppBundle\Factory\PatientFactoryInterface;
use AppBundle\Lexer\LexerInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Finder\Finder;

class HL7ADTParserCommand extends ContainerAwareCommand
{
    const SAVE_OPTION = 'save';
    const DUMP_OPTION = 'dump';
    // I work on a windows as my computer with linux and docker installed just died... RIP
    const FOLDER_PATH = __DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR .'..'.DIRECTORY_SEPARATOR .'..'.DIRECTORY_SEPARATOR.'messages';

    protected function configure()
    {
        $this
            ->setName('hl7adt:parse')
            ->setAliases([
                'h7a:p'
            ])
            ->addOption(self::SAVE_OPTION, null, InputOption::VALUE_NONE, 'Enable persist database.')
            ->addOption(self::DUMP_OPTION, null, InputOption::VALUE_NONE, 'Dump the messages.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $finder = new Finder();
        $path = $this->getContainer()->getParameter('hl7.adt.message_folder');

        $files = $finder->in($path)->getIterator();
        $lexer = $this->getContainer()->get(LexerInterface::class);
        $style = new SymfonyStyle($input, $output);

        foreach ($files as $file) {
            $style->note(sprintf('Handling %s file', $file->getPathname()));

            $content = $file->getContents();
            $message = $lexer->tokenize($content);

            $this->handleMessage($message, $input, $style);
        }

        if ($input->hasOption(self::SAVE_OPTION) && $input->getOption(self::SAVE_OPTION) !== false) {
            $style->success('Messages saved.');
        }

        $style->success('Messages successfully treated.');
    }

    private function handleMessage($message, InputInterface $input, SymfonyStyle $style = null)
    {
        $doctorFactory = $this->getContainer()->get(DoctorFactoryInterface::class);
        $patientFactory = $this->getContainer()->get(PatientFactoryInterface::class);

        try {
            $doctor = $doctorFactory->create($message);
            $patient = $patientFactory->create($message);

            $patient->setDoctor($doctor);

            if ($input->hasOption(self::DUMP_OPTION) && $input->getOption(self::DUMP_OPTION) !== false) {
                print_r($patient);
            }

            if ($input->hasOption(self::SAVE_OPTION) && $input->getOption(self::SAVE_OPTION) !== false) {
                $em = $this->getContainer()->get('doctrine.orm.default_entity_manager');

                $em->persist($patient);
                $em->flush();

            }
        } catch (FactoryMessageNotSupportedException $exception) {
            $this->getContainer()->get('logger')->error($exception->getMessage());
            $style->error($exception->getMessage());
        }
    }
}