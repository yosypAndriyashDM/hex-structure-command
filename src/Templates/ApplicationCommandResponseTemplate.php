<?php

namespace YosypPro\HexagonalStructureCommand\Templates;

class ApplicationCommandResponseTemplate extends BasePhpClassTemplate
{
    public function generateParsedTemplateOutput(): string|null
    {
        $replaceInTemplate['class_name'] = $this->className;
        $replaceInTemplate['use_definitions'] = $this->generateUseDefinitions($this->useDefinitions);
        $replaceInTemplate['namespace'] = $this->classNamespace = $this->calculateNamespaceForPhpClass($this->filePath);

        $replaceInTemplate['application_command_response_construct_class_parameters'] = $this->generateClassConstructParams(
            $this->commandInputContainer->getInput('packageApplicationActionCommandResponseParams')
        );

        $replaceInTemplate['application_command_response_parameters_getters'] = $this->generateClassAttributesGetters(
            $this->commandInputContainer->getInput('packageApplicationActionCommandResponseParams')
        );

        return $this->parseTemplatePlaceholders($replaceInTemplate);
    }
}