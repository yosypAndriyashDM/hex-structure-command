<?php

namespace App\Command\HexagonalStructureCommand\Trait;

use App\Command\HexagonalStructureCommand\CommandInputContainer;
use App\Command\HexagonalStructureCommand\Templates\BasePhpClassTemplate;
use RuntimeException;

trait CommandBodyStepsTrait {

    /** @var $commandInputContainer CommandInputContainer  */
    protected $commandInputContainer;

    protected $isDebuggingMode = false;

    /**
     * @throws RuntimeException
     */
    private function loadYamlCommandConfigurationStep(): void
    {
        $title = '> Loading command configuration file... ("' . $this->configurationFile . '")';

        $this->writeLine($this->repeatChar(strlen($title), '-'));
        $this->writeLine($title);

        $configuration = $this->loadYaml($this->configurationFile);

        if (!$configuration) {
            throw new RuntimeException('Invalid YAML configuration');
        }

        $namespaceConfig = $configuration['namespaceConfig'] ?? null;
        if (!$namespaceConfig) {
            throw new RuntimeException('Missing namespace configuration');
        }

        $templatesConfig = $configuration['templates'] ?? null;
        if (!$templatesConfig) {
            throw new RuntimeException('Missing templates configuration');
        }

        $this->configurationData = $configuration;
        $this->writeSuccess('> Command configuration file was successfully loaded...');

        $this->writeLine($this->repeatChar(strlen($title), '-'));
        $this->writeLine();
    }

    /**
     * @throws RuntimeException
     */
    private function packageNameInputStep(): void
    {
        try {
            $packageNameUserResponse = trim($this->getUserInputRequest('Type your root package name, for example: Product'));
            $packageNameUserResponse = $this->formatToLowerCamelCase($packageNameUserResponse);

            if ($packageNameUserResponse === self::DEBUG_MAGIC_WORD) {
                $this->isDebuggingMode = true;
                $packageNameUserResponse .= time();
            }

            $this->ensureUserInputResponseIsValid($packageNameUserResponse);

            $this->commandInputContainer->addInput(
                'packageName',
                $packageNameUserResponse
            );

            $this->commandInputContainer->addInput(
                'packageRootPath',
                $this->projectPath . $this->configurationData['rootPath']
                . ucfirst($this->formatToLowerCamelCase($this->commandInputContainer->getInput('packageName')))
                . DIRECTORY_SEPARATOR
            );

            // Store package paths to create it after:
            $modelPath = $this->commandInputContainer->getInput('packageRootPath') . 'Model' . DIRECTORY_SEPARATOR;
            $applicationPath = $modelPath . 'Application' . DIRECTORY_SEPARATOR;
            $domainPath = $modelPath . 'Domain' . DIRECTORY_SEPARATOR;

            $this->commandInputContainer->addInput('packageModelPath', $modelPath);
            $this->commandInputContainer->addInput('packageApplicationPath', $applicationPath);
            $this->commandInputContainer->addInput('packageDomainPath', $domainPath);

        } catch (RuntimeException $exception) {
            throw new RuntimeException('INVALID PACKAGE NAME ON STEP 1: "' . $exception->getMessage() . '"');
        }
    }

    private function packageEntityNameInputStep(): void
    {
        try {
            if ($this->isDebuggingMode === true) {
                $packageEntityNameUserResponse = self::DEBUG_PACKAGE_ENTITY_NAME;
            } else {
                $packageEntityNameUserResponse = trim($this->getUserInputRequest('Type your Package Entity name, for example: Laptop'));
            }

            $packageEntityNameUserResponse = ucfirst($this->formatToLowerCamelCase($packageEntityNameUserResponse));
            $this->ensureUserInputResponseIsValid($packageEntityNameUserResponse);

            $this->commandInputContainer->addInput(
                'packageEntityName',
                $packageEntityNameUserResponse
            );

            $this->commandInputContainer->addInput(
                'packageApplicationEntityPath',
                $this->commandInputContainer->getInput('packageApplicationPath') . $packageEntityNameUserResponse . DIRECTORY_SEPARATOR
            );

            $this->commandInputContainer->addInput(
                'packageDomainEntityPath',
                $this->commandInputContainer->getInput('packageDomainPath') . $packageEntityNameUserResponse . DIRECTORY_SEPARATOR
            );

            $this->commandInputContainer->addInput(
                'packageDomainEntityModelPath',
                $this->commandInputContainer->getInput('packageDomainEntityPath') . 'Entity' . DIRECTORY_SEPARATOR
            );

            $this->commandInputContainer->addInput(
                'packageDomainEntityServicePath',
                $this->commandInputContainer->getInput('packageDomainEntityPath') . 'Service' . DIRECTORY_SEPARATOR
            );

            $this->commandInputContainer->addInput(
                'packageDomainEntityRepositoryPath',
                $this->commandInputContainer->getInput('packageDomainEntityPath') . 'Repository' . DIRECTORY_SEPARATOR
            );

            $this->commandInputContainer->addInput(
                'packageDomainEntityExceptionPath',
                $this->commandInputContainer->getInput('packageDomainEntityPath') . 'Exception' . DIRECTORY_SEPARATOR
            );

        } catch (RuntimeException) {

        }
    }

    private function createPackageFilesStep(): void
    {
        $templatesConfig = $this->configurationData['templates'];
        $packageActionType = $this->commandInputContainer->getInput('packageApplicationActionType');
        $classFilesToParse = [];

        // QUERY/READ FILES
        if ($packageActionType === self::PACKAGE_ACTION_TYPE_READ_AND_COMMAND || $packageActionType === self::PACKAGE_ACTION_TYPE_READ) {

            $readConfig = $templatesConfig['read'];
            $applicationQueryPath = $this->commandInputContainer->getInput('packageApplicationEntityQueryPath');

            foreach ($readConfig as $key => $value) {
                $className = ucfirst($this->commandInputContainer->getInput('packageAction')) . $value['suffix'];
                $fileName = $className . '.php';
                $filePath = $applicationQueryPath . $fileName;

                $classFilesToParse[] = array_merge($value, [
                    'filePath' => $filePath,
                    'className' => $className
                ]);
            }
        }

        // COMMAND FILES
        if ($packageActionType === self::PACKAGE_ACTION_TYPE_READ_AND_COMMAND || $packageActionType === self::PACKAGE_ACTION_TYPE_COMMAND) {
            $commandConfig = $templatesConfig['command'];
            $applicationCommandPath = $this->commandInputContainer->getInput('packageApplicationEntityCommandPath');

            foreach ($commandConfig as $key => $value) {
                $className = ucfirst($this->commandInputContainer->getInput('packageAction')) . $value['suffix'];
                $fileName = $className . '.php';
                $filePath = $applicationCommandPath . $fileName;

                $classFilesToParse[] = array_merge($value, [
                    'filePath' => $filePath,
                    'className' => $className
                ]);
            }
        }

        // DOMAIN & ENTITY FILES
        $domainConfig = $templatesConfig['domain'];

        foreach ($domainConfig as $key => $value) {
            $className = match ($key) {
                'repositoryInterface', 'entity', 'entityCollection' => $this->commandInputContainer->getInput('packageEntityName'),
                'service' => $this->commandInputContainer->getInput('packageAction')
            };

            $filePath = match ($key) {
                'repositoryInterface' => $this->commandInputContainer->getInput('packageDomainEntityRepositoryPath'),
                'service' => $this->commandInputContainer->getInput('packageDomainEntityServicePath'),
                'entity', 'entityCollection' => $this->commandInputContainer->getInput('packageDomainEntityModelPath')
            };

            $className = ucfirst($className) . $value['suffix'];
            $fileName = $className . '.php';
            $filePath = $filePath . $fileName;

            $classFilesToParse[] = array_merge($value, [
                'filePath' => $filePath,
                'className' => $className
            ]);
        }

        $title = 'Creating package files:';
        $this->writeLine();
        $this->writeSuccess($this->repeatChar(64, '-'));
        $this->writeSuccess($title);
        $this->writeSuccess($this->repeatChar(64, '-'));

        $classFilesToParse = BasePhpClassTemplate::sortByDependencies($classFilesToParse);
        $createdInstances = [];

        foreach ($classFilesToParse as $item) {

            $templateDispatcher = BasePhpClassTemplate::getNamespace() . '\\' . $item['templateClassDispatcher'];
            if (!class_exists($templateDispatcher)) {
                $this->writeComment('Template parser for class template ' . $item['templateClassDispatcher'] . ' not found, please create this file! [continue...]');
                continue;
            }

            try {
                /** @var BasePhpClassTemplate $templateDispatcher */
                $instance = new $templateDispatcher(
                    $this->configurationData,
                    $this->commandInputContainer,
                    $this->getTemplateContent($item['template']),
                    $item,
                    $createdInstances
                );

                $createdInstances[$item['templateClassDispatcher']] = $instance;
                $parsedTemplate = $instance->generateParsedTemplateOutput();

                $successClassFileCreated = $this->createFile($item['filePath'], $parsedTemplate);
            } catch (RuntimeException $exception) {

            }
        }
    }

    private function packageApplicationActionInputStep(): void
    {
        if ($this->isDebuggingMode === true) {
            $packageActionUserResponse = self::DEBUG_PACKAGE_ACTION_NAME;
        } else {
            $packageActionUserResponse = $this->getUserInputRequest('Type your package action, for example: "get active contacts history" or "GetActiveContacts"');
        }

        $packageActionUserResponse = $this->formatToLowerCamelCase($packageActionUserResponse);

        $this->ensureUserInputResponseIsValid($packageActionUserResponse);

        $this->commandInputContainer->addInput(
            'packageAction',
            $packageActionUserResponse
        );
    }

    private function packageApplicationActionTypeInputStep(): void
    {
        $this->writeTitle('Select action type for "' . $this->commandInputContainer->getInput('packageAction') . '":');
        $this->writeLine('1: Read (get data)');
        $this->writeLine('2: Command (put data)');
        $this->writeLine('3: Read & command (get and put data)');

        if ($this->isDebuggingMode === true) {
            $packageApplicationActionTypeUserResponse = self::PACKAGE_ACTION_TYPE_READ_AND_COMMAND;
        } else {
            $packageApplicationActionTypeUserResponse = (int) $this->getUserInputRequest('');
        }

        // Define and get it from yaml config
        $allowedResponse = [
            self::PACKAGE_ACTION_TYPE_READ,
            self::PACKAGE_ACTION_TYPE_COMMAND,
            self::PACKAGE_ACTION_TYPE_READ_AND_COMMAND
        ];

        if (!in_array($packageApplicationActionTypeUserResponse, $allowedResponse)) {
            throw new RuntimeException('Invalid action type response');
        }

        $this->commandInputContainer->addInput('packageApplicationActionType', $packageApplicationActionTypeUserResponse);

        /* Create Application Action/{type} path */
        if (in_array($packageApplicationActionTypeUserResponse, [
            self::PACKAGE_ACTION_TYPE_READ, self::PACKAGE_ACTION_TYPE_READ_AND_COMMAND
        ], true)) {
            $this->commandInputContainer->addInput(
                'packageApplicationEntityQueryPath',
                $this->commandInputContainer->getInput('packageApplicationEntityPath') . 'Read' . DIRECTORY_SEPARATOR
            );
        }

        if (in_array($packageApplicationActionTypeUserResponse, [
            self::PACKAGE_ACTION_TYPE_COMMAND, self::PACKAGE_ACTION_TYPE_READ_AND_COMMAND
        ], true)) {
            $this->commandInputContainer->addInput(
                'packageApplicationEntityCommandPath',
                $this->commandInputContainer->getInput('packageApplicationEntityPath') . 'Command' . DIRECTORY_SEPARATOR
            );
        }
    }

    private function packageApplicationActionReadArgsStep():void
    {
        // Define application query params
        $this->writeTitle('Define query filter params');
        $this->writeLine('Type one by one your query params (for example: id, name, phoneNumber)');
        $this->writeComment('Please use camelCase or separated by space format: "phoneNumber" or "phone number"');

        if ($this->isDebuggingMode === true) {
            $readQueryParams = self::DEBUG_DATA_PARAMETERS;
        } else {
            $readQueryParams = $this->getUserPackageApplicationParameters();
        }

        $this->commandInputContainer->addInput('packageApplicationActionReadQueryParams', $readQueryParams);

        if (count($readQueryParams) > 0 && !$this->isDebuggingMode) {
            // add each query param type
            $this->getUserPackageApplicationParametersTypes($readQueryParams);
            $this->commandInputContainer->addInput('packageApplicationActionReadQueryParams', $readQueryParams);
        }

        // Define entity attributes
        $entityName = $this->commandInputContainer->getInput('packageEntityName');
        $this->writeTitle('Define "' . $entityName . '" entity class attributes');
        $this->writeLine('Type one by one your expected response keys (for example: id, name, phoneNumber)');
        $this->writeComment('Please use camelCase or separated by space format: "phoneNumber" or "phone number"');


        if ($this->isDebuggingMode === true) {
            $params = self::DEBUG_DATA_PARAMETERS;
        } else {
            $params = $this->getUserPackageApplicationParameters();
        }

        $this->commandInputContainer->addInput('packageDomainEntityAttributes', $params);

        // Define response params types
        // check first if not empty
        if (count($params) > 0 && !$this->isDebuggingMode) {
            $this->getUserPackageApplicationParametersTypes($params);
            $this->commandInputContainer->addInput('packageDomainEntityAttributes', $params);
        }

        // Check if response is an item collection (array)
        $this->writeTitle('Response collection');
        $this->writeLine('Is the response of the application-read a data collection (array)?');
        $this->writeComment('Select option:');
        $this->writeLine('1: YES, is a data collection response');
        $this->writeLine('2: NO, is not a data collection response');

        // if (1): create EntityList class as list of Entity+ items
        if ($this->isDebuggingMode === true) {
            $applicationReadResponseIsList = self::APPLICATION_RESPONSE_IS_DATA_COLLECTION;
        } else {
            $applicationReadResponseIsList = $this->packageApplicationCheckDataIsListSubStep();
        }

        $this->commandInputContainer->addInput('packageApplicationActionReadResponseIsCollection', $applicationReadResponseIsList);
    }

    private function packageApplicationActionCommandArgsStep():void
    {
        // Define command params (POST, PUT, PATCH)
        $this->writeTitle('Define your command keys (post variables)');
        $this->writeLine('Type one by one your command keys (for example: name, sur_name, birthdate, country)');
        $this->writeComment('Please use camelCase or separated by space format: "phoneNumber" or "phone number"');

        if ($this->isDebuggingMode === true) {
            $params = self::DEBUG_DATA_PARAMETERS;
        } else {
            $params = $this->getUserPackageApplicationParameters();
        }

        $this->commandInputContainer->addInput('packageApplicationActionCommandParams', $params);

        if (count($params) > 0 && !$this->isDebuggingMode) {
            // define each command-key type
            $this->getUserPackageApplicationParametersTypes($params);
            $this->commandInputContainer->addInput('packageApplicationActionCommandParams', $params);

            $this->writeTitle('Command parameters type');
            $this->writeLine('Is the command parameters a data collection (array)? (Post multiple data...)');
            $this->writeComment('Select one of the above:');
            $this->writeLine('1: YES, is a data collection command');
            $this->writeLine('2: NO, is not a data collection command');

            $applicationCommandParametersIsList = $this->packageApplicationCheckDataIsListSubStep();
            $this->commandInputContainer->addInput('packageApplicationActionCommandParametersIsCollection', $applicationCommandParametersIsList);
        }

        $this->writeTitle('Define your expected command response attributes');
        $this->writeLine('Type one by one your expected command response keys (for example: id, name, phoneNumber)');
        $this->writeComment('Please use camelCase or separated by space format: "phoneNumber" or "phone number"');

        if ($this->isDebuggingMode === true) {
            $params = self::DEBUG_DATA_PARAMETERS;
        } else {
            $params = $this->getUserPackageApplicationParameters();
        }

        $this->commandInputContainer->addInput('packageApplicationActionCommandResponseParams', $params);

        // Define command response params types
        if (count($params) > 0 && !$this->isDebuggingMode) {

            $this->getUserPackageApplicationParametersTypes($params);
            $this->commandInputContainer->addInput('packageApplicationActionCommandResponseParams', $params);
        }

        $this->writeTitle('Command Response type');
        $this->writeLine('Is the response of the application-command a data collection (array)?');
        $this->writeComment('Select one of the above:');
        $this->writeLine('1: YES, is a data collection response');
        $this->writeLine('2: NO, is not a data collection response');

        if ($this->isDebuggingMode === true) {
            $applicationCommandResponseIsList = self::APPLICATION_RESPONSE_IS_DATA_COLLECTION;
        } else {
            $applicationCommandResponseIsList = $this->packageApplicationCheckDataIsListSubStep();
        }

        $this->commandInputContainer->addInput('packageApplicationActionCommandResponseIsCollection', $applicationCommandResponseIsList);
    }

    private function packageApplicationCheckDataIsListSubStep(): int
    {
        try {
            $userResponse = (int) trim($this->getUserInputRequest());

            if (!in_array($userResponse, [1, 2])) {
                throw new RuntimeException('Invalid response');
            }

            return $userResponse;

        } catch (RuntimeException) {
            $this->writeError('Invalid answer, must be 1 or 2');
            $this->packageApplicationCheckDataIsListSubStep();
        }
    }

    private function createPackagePathsStep(): void
    {
        try {

            // Create PackageRoot/ path
            $toCreateDirs[] = $this->commandInputContainer->getInput('packageRootPath');

            // Create PackageRoot/Model/ path
            $toCreateDirs[] = $this->commandInputContainer->getInput('packageModelPath');

            // Create PackageRoot/Model/Application path
            $toCreateDirs[] = $this->commandInputContainer->getInput('packageApplicationPath');

            // Create PackageRoot/Model/Application/PackageAction path
            $toCreateDirs[] = $this->commandInputContainer->getInput('packageApplicationEntityPath');

            // Package application entity  -> read & command

            // Create PackageRoot/Model/Domain path
            $toCreateDirs[] = $this->commandInputContainer->getInput('packageDomainPath');

            // Create PackageRoot/Model/Domain/PackageAction path
            $toCreateDirs[] = $this->commandInputContainer->getInput('packageDomainEntityPath');

            // Create PackageRoot/Model/Domain/Model path
            $toCreateDirs[] = $this->commandInputContainer->getInput('packageDomainEntityModelPath');

            // Create PackageRoot/Model/Domain/Service path
            $toCreateDirs[] = $this->commandInputContainer->getInput('packageDomainEntityServicePath');

            // Create PackageRoot/Model/Domain/Repository path
            $toCreateDirs[] = $this->commandInputContainer->getInput('packageDomainEntityRepositoryPath');

            // Create PackageRoot/Model/Domain/PackageAction/Exceptions path
            $toCreateDirs[] = $this->commandInputContainer->getInput('packageDomainEntityExceptionPath');
            // $packageDomainActionExceptionsPath = $this->commandInputContainer->getInput('packageDomainEntityExceptionsPath');
            // $this->createDir($packageDomainActionExceptionsPath);

            // Create PackageRoot/Model/Application/PackageAction/Read and Command oath
            if ($this->commandInputContainer->existsInput('packageApplicationEntityQueryPath')) {
                $toCreateDirs[] = ($this->commandInputContainer->getInput('packageApplicationEntityQueryPath'));
            }

            if ($this->commandInputContainer->existsInput('packageApplicationEntityCommandPath')) {
                $toCreateDirs[] = ($this->commandInputContainer->getInput('packageApplicationEntityCommandPath'));
            }

            $title = 'Creating package dirs:';
            $this->writeLine();
            $this->writeSuccess($this->repeatChar(64, '-'));
            $this->writeSuccess($title);
            $this->writeSuccess($this->repeatChar(64, '-'));

            foreach ($toCreateDirs as $dir) {
                $this->createDir($dir);
            }

        } catch (RuntimeException $exception) {
            throw new RuntimeException('Can not create package path: ' . $exception->getMessage());
        }
    }
}