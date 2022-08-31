<?php

namespace YosypPro\HexagonalStructureCommand\Templates;

class DomainEntityTemplate extends BasePhpClassTemplate
{
    public function generateParsedTemplateOutput(): string|null
    {
        $replaceInTemplate['class_name'] = $this->className;
        $replaceInTemplate['use_definitions'] = $this->generateUseDefinitions($this->useDefinitions);
        $replaceInTemplate['namespace'] = $this->classNamespace = $this->calculateNamespaceForPhpClass($this->filePath);

        $replaceInTemplate['domain_class_construct_params'] = $this->generateClassConstructParams(
            $this->commandInputContainer->getInput('packageDomainEntityAttributes')
        );

        $replaceInTemplate['domain_class_create_params'] = $this->generateCreateMethodConstructParams();
        $replaceInTemplate['domain_class_create_arguments'] = $this->generateCreateMethodConstructArguments();

        $replaceInTemplate['domain_class_getters'] = $this->generateClassAttributesGetters(
            $this->commandInputContainer->getInput('packageDomainEntityAttributes')
        );

        return $this->parseTemplatePlaceholders($replaceInTemplate);
    }

    private function generateCreateMethodConstructParams(): string
    {
        $entityAttributes = $this->commandInputContainer->getInput('packageDomainEntityAttributes');
        $output = [];

        foreach ($entityAttributes as $attribute) {
            $output[] = $this->drawTabSpace(2) . $attribute['type'] . ' $' . $attribute['name'];
        }

        return implode(',' . PHP_EOL, $output);
    }

    private function generateCreateMethodConstructArguments(): string
    {
        $entityAttributes = $this->commandInputContainer->getInput('packageDomainEntityAttributes');
        $output = [];

        foreach ($entityAttributes as $attribute) {
            $output[] = $this->drawTabSpace(3) . $attribute['name'] . ': $' . $attribute['name'];
        }

        return implode(',' . PHP_EOL, $output);
    }
}