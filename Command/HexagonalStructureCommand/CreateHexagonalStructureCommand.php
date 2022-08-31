<?php

namespace App\Command\HexagonalStructureCommand;

use App\Command\HexagonalStructureCommand\Trait\FileTrait;
use App\Command\HexagonalStructureCommand\Trait\IOTrait;
use RuntimeException;
use App\Command\HexagonalStructureCommand\Trait\CommandBodyStepsTrait;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Exception\ParseException;

// the name of the command is what users type after "php bin/console"
#[AsCommand(name: 'app:create-hex-structure')]

class CreateHexagonalStructureCommand extends Command
{
    use IOTrait;
    use FileTrait;
    use CommandBodyStepsTrait;

    protected static $defaultName = 'app:create-hex-structure';
    protected static $defaultDescription = 'Creates a new hexagonal structure';

    private const DEBUG_MAGIC_WORD = 'test';
    private const DEBUG_PACKAGE_ENTITY_NAME = 'test entity name';
    private const DEBUG_PACKAGE_ACTION_NAME = 'get test package action';

    private const DEBUG_DATA_PARAMETERS = [
        ['name' => 'id', 'type' => 'int'],
        ['name' => 'name', 'type' => 'string'],
        ['name' => 'surName', 'type' => '']
    ];

    private const PACKAGE_ACTION_TYPE_READ = 1;
    private const PACKAGE_ACTION_TYPE_COMMAND = 2;
    private const PACKAGE_ACTION_TYPE_READ_AND_COMMAND = 3;

    private const APPLICATION_RESPONSE_IS_DATA_COLLECTION = 1;
    private const APPLICATION_RESPONSE_IS_NOT_DATA_COLLECTION = 2;

    private const PACKAGE_DATA_TYPE_INT = 'int';
    private const PACKAGE_DATA_TYPE_STRING = 'string';
    private const PACKAGE_DATA_TYPE_BOOL = 'bool';
    private const PACKAGE_DATA_TYPE_FLOAT = 'float';
    private const PACKAGE_DATA_TYPE_OTHER = 'empty';

    private const PACKAGE_DATA_TYPE_DEFAULT = self::PACKAGE_DATA_TYPE_OTHER;

    /** @var $input \Symfony\Component\Console\Input\InputInterface */
    private $input = null;

    /** @var $output \Symfony\Component\Console\Output\OutputInterface */
    private $output = null;

    /** @var $helper QuestionHelper */
    private $helper = null;

    /** @var $commandInputContainer CommandInputContainer  */
    protected $commandInputContainer = null;

    private $projectPath = null;
    private $commandPath = null;
    private $configurationFile = 'command_configuration.yaml';
    private array $configurationData;

    protected function configure(): void
    {
        // --help
        $this->setHelp('This command allows you to implement in couple of seconds a new hexagonal structure for your vendor sub-project...');

        // Arguments for the command:
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->input = $input;
        $this->output = $output;
        $this->helper = $this->getHelper('question');
        $this->commandInputContainer = new CommandInputContainer();

        $this->projectPath = realpath(dirname(__DIR__).'/../..') . DIRECTORY_SEPARATOR;
        $this->commandPath = realpath(__DIR__) . DIRECTORY_SEPARATOR;

        $commandStartedAt = microtime(true);

        $this->writeLine();
        $this->writeTitle([
            '',
            'Welcome to DIGI "HexagonalStructureCreator"',
            'v.1.0.1 - First Release (2022SP)',
            '',
            $this->repeatChar(64, '-'),
            '',
            'Support contact:',
            '',
            'yosyp.andriyash@digimobil.es',
            'alberto.luna@digimobil.es',
            '',
        ]);

        // Command (BODY)
        try {

            // Body
            $this->commandBody();
            $output = Command::SUCCESS;

        } catch (RuntimeException $exception) {

            $this->writeError($exception->getMessage());
            $output = Command::FAILURE;
        }

        $this->writeLine();
        $this->writeTitle([
            '',
            'Command execution finished... (' . (number_format(microtime(true) - $commandStartedAt, 4)) . 'ms)',
            '',
            $this->repeatChar(64, '-'),
            '',
            'Have a nice day :)',
            ''
        ]);

        return $output;
    }

    /**
     * @throws RuntimeException
     */
    private function commandBody(): void
    {
        /**
         * Body was divided in command steps,
         * Each step was independent function
         * ALL COMMAND STEPS MUST BE CALLED HERE
         */
        // Command configuration load & verification
        $this->loadYamlCommandConfigurationStep();

        //$this->parseTemplates(); die;

        // PackageName input and root dirs creation
        $this->packageNameInputStep();
        $this->packageEntityNameInputStep();

        $this->packageApplicationActionInputStep();
        $this->packageApplicationActionTypeInputStep();

        $packageApplicationActionType = (int) $this->commandInputContainer->getInput('packageApplicationActionType');

        if (in_array($packageApplicationActionType, [
            self::PACKAGE_ACTION_TYPE_READ, self::PACKAGE_ACTION_TYPE_READ_AND_COMMAND
        ], true)) {

            $this->packageApplicationActionReadArgsStep();
        }

        if (in_array($packageApplicationActionType, [
            self::PACKAGE_ACTION_TYPE_COMMAND, self::PACKAGE_ACTION_TYPE_READ_AND_COMMAND
        ], true)) {

            $this->packageApplicationActionCommandArgsStep();
        }

        $this->createPackagePathsStep();
        $this->createPackageFilesStep();

        /*
         * NO MORE STEPS FROM HERE IN ABOVE
         */

        // Finish command step
    }

    // Steps definitions
    // ------------------------------------

    private function getUserPackageApplicationParameters(&$definedParams = []): array
    {
        // Do question
        $this->writeLine('Press "Enter" to add or "0" (zero number) to finish');

        $newParam = trim($this->getUserInputRequest());

        if ($newParam !== '0') {
            try {
                $newParam = $this->formatToLowerCamelCase($newParam);

                // Throws exception if response is not valid
                $this->ensureUserInputResponseIsValid($newParam, $definedParams);

                // If response is not valid next step never occurs
                $definedParams[] = ['name' => $newParam, 'type' => ''];

            } catch (RuntimeException $exception) {
                $this->writeError($exception->getMessage());
            }

            // Self call
            $this->getUserPackageApplicationParameters($definedParams);
        }

        return $definedParams;
    }

    public function getUserPackageApplicationParametersTypes(&$currentParams = [], &$index = 0): void
    {
        if ($index < count($currentParams)) {
            $currentParam = $currentParams[$index]['name'] ?? null;

            $this->writeLine('Define param "' . $currentParam . '" type (int, string, bool, float, other) (Default ' . self::PACKAGE_DATA_TYPE_DEFAULT . ')');
            $currentParamType = trim($this->getUserInputRequest());

            // Validate...
            if (!in_array($currentParamType, [
                self::PACKAGE_DATA_TYPE_INT,
                self::PACKAGE_DATA_TYPE_STRING,
                self::PACKAGE_DATA_TYPE_BOOL,
                self::PACKAGE_DATA_TYPE_FLOAT,
                self::PACKAGE_DATA_TYPE_OTHER
            ])) {
                $currentParamType = self::PACKAGE_DATA_TYPE_DEFAULT;
            }

            $currentParams[$index]['type'] = ($currentParamType === self::PACKAGE_DATA_TYPE_OTHER) ? '' : $currentParamType;
            $index++;
            $this->getUserPackageApplicationParametersTypes($currentParams, $index);
        }
    }

    private function ensureUserInputResponseIsValid($userResponseString, $currentResponsesList = []): void
    {
        // Validate param
        if ($userResponseString === '' || strlen($userResponseString) > 32) {
            throw new RuntimeException('Invalid argument length, must be in 2 - 32 chars');
        }

        // Validate was not numeric
        if (!preg_match('/^[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]/', $userResponseString)) {
            throw new RuntimeException('Invalid name, use letters and numbers commencing by letter');
        }

        // check if repeated
        if (in_array($userResponseString, $currentResponsesList, true)) {
            throw new RuntimeException('Repeated argument');
        }
    }

    /**
     * @throws RuntimeException
     */
    private function loadYaml($fileName)
    {
        // configuration file must be in config/commands/ path
        $filePath = $this->commandPath . 'Configuration/' . $fileName;

        try {
            return YamlConfigurationHelper::loadConfiguration($filePath, 'commandConfiguration');
        } catch (ParseException $exception) {
            throw new RuntimeException('Unable to parse the YAML string: ' .  $exception->getMessage());
        }
    }

    private function formatToLowerCamelCase($string)
    {
        $replacers = ['_', '-', ' '];
        foreach ($replacers as $replacer) {
            if (str_contains($string, $replacer)) {
                return StringHelper::toLowerCamelCase($string, $replacer);
            }
        }

        return $string;
    }
}