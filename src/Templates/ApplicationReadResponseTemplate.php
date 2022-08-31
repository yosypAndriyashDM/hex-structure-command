<?php

namespace YosypPro\HexagonalStructureCommand\Templates;

class ApplicationReadResponseTemplate extends BasePhpClassTemplate
{
    public function generateParsedTemplateOutput(): string|null
    {
        $replaceInTemplate['class_name'] = $this->className;
        $replaceInTemplate['use_definitions'] = $this->generateUseDefinitions($this->useDefinitions);
        $replaceInTemplate['namespace'] = $this->classNamespace = $this->calculateNamespaceForPhpClass($this->filePath);

        $replaceInTemplate['application_read_response_constructor_args'] = $this->generateClassConstructParams(
            $this->commandInputContainer->getInput('packageDomainEntityAttributes')
        );

        $replaceInTemplate['application_read_response_getters_body'] = $this->generateClassAttributesGetters(
            $this->commandInputContainer->getInput('packageDomainEntityAttributes')
        );

        return $this->parseTemplatePlaceholders($replaceInTemplate);
    }
}