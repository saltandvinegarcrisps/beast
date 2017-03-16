<?php

namespace Beast\Framework\Commands;

use Beast\Framework\Support\Paths;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;

class MigrationsCreate extends Command
{
    protected $paths;

    public function __construct(Paths $paths)
    {
        $this->paths = $paths;
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('migrate:create')
            ->setDescription('Create a new migration')
            ->setDefinition(
                new InputDefinition([
                    new InputArgument('name', InputArgument::REQUIRED, 'The migration name'),
                ])
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $rawName = $input->getArgument('name');

        $name = str_replace(['_', '-'], ' ', strtolower($rawName));

        $className = str_replace(' ', '', ucwords(strtolower($rawName)));

        $path = $this->paths->resolve('app/migrations');

        $template = '<?php

use Doctrine\DBAL\Schema\Schema;

class %s
{
	public function up(Schema $schema)
	{
		// ...
	}

	public function down(Schema $schema)
	{
		// ...
	}
}
';

        file_put_contents(sprintf('%s/%s_%s.php', $path, $name, time()), sprintf($template, $className));

        $output->writeln(sprintf('<info>Created migration %s</info>', $name));
    }
}
