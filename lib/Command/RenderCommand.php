<?php

declare(strict_types=1);

namespace Doctrine\RST\Command;

use Doctrine\RST\Builder;
use Doctrine\RST\Configuration;
use Doctrine\RST\Formats\Format;
use RuntimeException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\FormatterHelper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use function assert;
use function getcwd;
use function str_starts_with;

class RenderCommand extends Command
{
    public function __construct()
    {
        parent::__construct('render');
    }

    protected function configure(): void
    {
        $this
            ->setHelp('This command allows you to render your documentation...')
            ->addOption(
                'deleteCache',
                'd',
                InputOption::VALUE_NONE,
                'Delete the cache before rendering?',
            )
            ->addOption(
                'format',
                'f',
                InputOption::VALUE_REQUIRED,
                'Format for the output: html or tex',
                'html'
            )
            ->addArgument('input', InputArgument::OPTIONAL, 'The input path, containing the index.rst')
            ->addArgument('output', InputArgument::OPTIONAL, 'The output path, the rendered files will be written here');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $configuration = new Configuration();
        $configuration->setFileExtension($input->getOption('format') === Format::LATEX ? Format::LATEX  : Format::HTML);
        $configuration->abortOnError(false);
        $configuration->setUseCachedMetas($input->getOption('deleteCache') === false);
        $configuration->setIgnoreInvalidReferences(true);

        $inputPath  = $this->getAbsolutePath($input->getArgument('input') ?? 'docs');
        $outputPath = $this->getAbsolutePath($input->getArgument('output') ?? 'output');

        $builder = new Builder($configuration);
        $builder->build($inputPath, $outputPath);

        $formatter = $this->getHelper('formatter');
        assert($formatter instanceof FormatterHelper);
        $message        = ['Docs rendered', 'Output written to ' . $outputPath];
        $formattedBlock = $formatter->formatBlock($message, 'info', true);
        $output->writeln($formattedBlock);

        return Command::SUCCESS;
    }

    private function getAbsolutePath(string $path): string
    {
        if (! str_starts_with($path, '/')) {
            if (getcwd() === false) {
                throw new RuntimeException('Cannot find current working directory, use absolute paths.');
            }

            $path = getcwd() . '/' . $path;
        }

        return $path;
    }
}
