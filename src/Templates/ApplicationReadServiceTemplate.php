<?php

namespace YosypPro\HexagonalStructureCommand\Templates;

class ApplicationReadServiceTemplate extends BasePhpClassTemplate
{
    public function generateParsedTemplateOutput(): string|null
    {
        /** @var BasePhpClassTemplate $repositoryTemplate */
        $repositoryTemplate = $this->dependencies['DomainEntityRepositoryTemplate'] ?? null;

        /** @var BasePhpClassTemplate $domainServiceTemplate */
        $domainServiceTemplate = $this->dependencies['DomainEntityServiceTemplate'] ?? null;

        /** @var BasePhpClassTemplate $applicationQueryTemplate */
        $applicationQueryTemplate = $this->dependencies['ApplicationReadQueryTemplate'] ?? null;

        /** @var BasePhpClassTemplate $applicationResponseTemplate */
        $applicationResponseTemplate = $this->dependencies['ApplicationReadResponseTemplate'] ?? null;

        $replaceInTemplate['class_name'] = $this->className;
        $replaceInTemplate['use_definitions'] = $this->generateUseDefinitions(
            array_merge(
                $this->useDefinitions, [
                    $this->getUseStatementForTemplateClass($domainServiceTemplate),
                    $this->getUseStatementForTemplateClass($repositoryTemplate),
                    $this->getUseStatementForTemplateClass($applicationQueryTemplate),
                    $this->getUseStatementForTemplateClass($applicationResponseTemplate)
                ])
        );

        $replaceInTemplate['namespace'] = $this->classNamespace = $this->calculateNamespaceForPhpClass($this->filePath);

        $replaceInTemplate['application_read_service_domain_service_definition'] = $this->generateDomainServicePropertyClassDefinition($domainServiceTemplate);
        $replaceInTemplate['application_read_service_class_constructor'] = $this->generateApplicationServiceConstructor();
        $replaceInTemplate['application_read_service_query_class_type'] = $this->drawTabSpace(2) . $applicationQueryTemplate->getClassName();
        $replaceInTemplate['application_read_service_execute_prepare_params_from_query'] = $this->generateIsolatedQueryParams();
        $replaceInTemplate['application_read_service_domain_call_stub'] = $this->generateDomainServiceCallStub();
        $replaceInTemplate['application_read_service_response_class_type'] = $applicationResponseTemplate->getClassName();
        $replaceInTemplate['application_read_service_response_domain_args'] = $this->generateResponseObjectConstructParams();

        return $this->parseTemplatePlaceholders($replaceInTemplate);
    }

    private function generateApplicationServiceConstructor(): string
    {
        /** @var BasePhpClassTemplate $repositoryTemplate */
        $repositoryTemplate = $this->dependencies['DomainEntityRepositoryTemplate'] ?? null;

        /** @var BasePhpClassTemplate $domainServiceTemplate */
        $domainServiceTemplate = $this->dependencies['DomainEntityServiceTemplate'] ?? null;

        return
            $this->drawTabSpace(1) . 'public function __construct(' . PHP_EOL .
            $this->drawTabSpace(2) . 'private ' . $repositoryTemplate->getClassName() . ' $' . lcfirst($repositoryTemplate->getClassName()) . ',' . PHP_EOL .
            $this->drawTabSpace(1) . ')' . PHP_EOL .
            $this->drawTabSpace(1) . '{' . PHP_EOL .
            $this->drawTabSpace(2) . '$this->' . lcfirst($domainServiceTemplate->getClassName()) . ' = new ' . $domainServiceTemplate->getClassName() .
                '($' . lcfirst($repositoryTemplate->getClassName()) . ');' . PHP_EOL .
            $this->drawTabSpace(1) . '}';
    }

    private function generateIsolatedQueryParams(): string
    {
        $data = $this->commandInputContainer->getInput('packageApplicationActionReadQueryParams');
        $output = '';

        foreach ($data as $item) {
            $output .= $this->drawTabSpace(2) . '$' . $item['name'] . ' = $query->get' . ucfirst($item['name']) . '();' . PHP_EOL;
        }

        return $output;
    }

    private function generateDomainServiceCallStub(): string
    {
        /** @var BasePhpClassTemplate $domainServiceTemplate */
        $domainServiceTemplate = $this->dependencies['DomainEntityServiceTemplate'] ?? null;

        $queryArguments = $this->commandInputContainer->getInput('packageApplicationActionReadQueryParams');
        $arguments = [];

        foreach ($queryArguments as $argument) {
            $arguments[] = '$' . $argument['name'];
        }

        return
            $this->drawTabSpace(2) . '$domainServiceResponse = $this->' . lcfirst($domainServiceTemplate->getClassName()) .
            '->execute(' . implode(', ', $arguments) . ');';
    }

    private function generateResponseObjectConstructParams(): string
    {
        $output = [];
        $responseParameters = $this->commandInputContainer->getInput('packageDomainEntityAttributes');

        foreach ($responseParameters as $parameter) {
            $output[] = $this->drawTabSpace(3) . '$domainServiceResponse->get' . ucfirst($parameter['name']) . '()';
        }

        return implode(',' . PHP_EOL, $output);
    }
}