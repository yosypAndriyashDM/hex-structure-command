<?php

namespace YosypPro\HexagonalStructureCommand\Templates;

class ApplicationCommandCommandTemplate extends BasePhpClassTemplate
{
    public function generateParsedTemplateOutput(): string|null
    {
        $replaceInTemplate['class_name'] = $this->className;
        $replaceInTemplate['use_definitions'] = $this->generateUseDefinitions($this->useDefinitions);
        $replaceInTemplate['namespace'] = $this->classNamespace = $this->calculateNamespaceForPhpClass($this->filePath);

        $replaceInTemplate['application_command_command_construct_class_parameters'] = $this->generateClassConstructParams(
            $this->commandInputContainer->getInput('packageApplicationActionCommandParams')
        );

        $replaceInTemplate['application_command_command_class_parameters_getters'] = $this->generateClassAttributesGetters(
            $this->commandInputContainer->getInput('packageApplicationActionCommandParams')
        );

        return $this->parseTemplatePlaceholders($replaceInTemplate);
    }
}