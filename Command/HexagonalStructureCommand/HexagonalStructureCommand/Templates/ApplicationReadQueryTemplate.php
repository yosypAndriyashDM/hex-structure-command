<?php

namespace App\Command\HexagonalStructureCommand\Templates;

class ApplicationReadQueryTemplate extends BasePhpClassTemplate
{
    public function generateParsedTemplateOutput(): string|null
    {
        $replaceInTemplate['class_name'] = $this->className;
        $replaceInTemplate['use_definitions'] = $this->generateUseDefinitions($this->useDefinitions);
        $replaceInTemplate['namespace'] = $this->classNamespace = $this->calculateNamespaceForPhpClass($this->filePath);

        $replaceInTemplate['application_read_query_construct_args'] = $this->generateClassConstructParams(
            $this->commandInputContainer->getInput('packageApplicationActionReadQueryParams')
        );

        $replaceInTemplate['application_read_query_getters_body'] = $this->generateClassAttributesGetters(
            $this->commandInputContainer->getInput('packageApplicationActionReadQueryParams')
        );

        return $this->parseTemplatePlaceholders($replaceInTemplate);
    }
}