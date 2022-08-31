<?php

namespace YosypPro\HexagonalStructureCommand\Templates;

use YosypPro\HexagonalStructureCommand\CommandInputContainer;

class DomainEntityRepositoryTemplate extends BasePhpClassTemplate
{
    public function generateParsedTemplateOutput(): string|null
    {
        $dependencyMatch = (
            // compare if is collection response or not
            // private CreateHexagonalStructureCommand::APPLICATION_RESPONSE_IS_DATA_COLLECTION
            $this->commandInputContainer->getInput('packageApplicationActionReadResponseIsCollection') === 1
        ) ? 'DomainEntityCollectionTemplate' : 'DomainEntityTemplate';

        /** @var BasePhpClassTemplate $domainEntityDependency */
        $domainEntityDependency = $this->dependencies[$dependencyMatch] ?? null;
        $domainEntityDependencyUseStatement = $domainEntityDependency->getClassNamespace() . '\\' . $domainEntityDependency->getClassName();

        $replaceInTemplate['class_name'] = $this->className;
        $replaceInTemplate['use_definitions'] = $this->generateUseDefinitions(
            $this->useDefinitions + [$domainEntityDependencyUseStatement]
        );
        $replaceInTemplate['namespace'] = $this->classNamespace = $this->calculateNamespaceForPhpClass($this->filePath);

        $responseDefinitionType = $this->commandInputContainer->getInput('packageApplicationActionType');
        $responseDefinitionType =
            ($responseDefinitionType === 2 || $responseDefinitionType === 3)
                ? 'packageApplicationActionCommandParams'
                : 'packageApplicationActionReadQueryParams';

        $replaceInTemplate['domain_entity_repository_methods_body'] = $this->generateEntityRepositoryMethods(
            [
                [
                    'action' => $this->commandInputContainer->getInput('packageAction'),
                    'arguments' => $this->commandInputContainer->getInput($responseDefinitionType),
                    'outputType' => $domainEntityDependency->getClassName()
                ]
            ]
        );

        return $this->parseTemplatePlaceholders($replaceInTemplate);
    }

    private function generateEntityRepositoryMethods(array $methods = [], string $output = ''): string
    {
        foreach ($methods as $method) {
            $methodArguments = [];
            foreach ($method['arguments'] as $argument) {
                $methodArguments[] = $argument['type'] . ' $' . $argument['name'];
            }

            $output .= $this->drawTabSpace(1) . 'public function ' . $method['action']
                . '(' . implode(', ', $methodArguments) . '): ' . $method['outputType'] . ';' . PHP_EOL;
            $output .= PHP_EOL;
        }

        return $output;
    }
}