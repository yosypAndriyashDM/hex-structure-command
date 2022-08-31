<?php

namespace App\Command\HexagonalStructureCommand\Templates;

class DomainEntityCollectionTemplate extends BasePhpClassTemplate
{
    public function generateParsedTemplateOutput(): string|null
    {
        $replaceInTemplate['class_name'] = $this->className;
        $replaceInTemplate['use_definitions'] = $this->generateUseDefinitions($this->useDefinitions);
        $replaceInTemplate['namespace'] = $this->classNamespace = $this->calculateNamespaceForPhpClass($this->filePath);

        return $this->parseTemplatePlaceholders($replaceInTemplate);
    }
}