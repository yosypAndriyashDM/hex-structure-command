<?php

namespace App\Command\HexagonalStructureCommand\Templates;

use App\Command\HexagonalStructureCommand\CommandInputContainer;

class BasePhpClassTemplate {

    protected string $className;
    protected string $outputString;
    protected array $commonPlaceholders = [
        'php_open_tag' => '<?php'
    ];

    protected array $templatePlaceholders = [];

    protected array $useDefinitions = [];

    protected string $filePath;

    protected string $classNamespace;

    public static function getNamespace(): string
    {
        return __NAMESPACE__;
    }

    public function __construct(
        protected array $configurationData,
        protected CommandInputContainer $commandInputContainer,
        protected string $templateContent,
        protected array $classData,
        protected array $dependencies
    )
    {
        $this->className = $classData['className'];
        $this->outputString = $templateContent;
        $this->useDefinitions = $classData['use'];
        $this->filePath = $this->classData['filePath'];
    }

    public function generateParsedTemplateOutput(): string|null
    {
        return null;
    }

    protected function parseTemplatePlaceholders(array $replacePlaceholders = []): string
    {
        $replacePlaceholders = array_merge($replacePlaceholders, $this->commonPlaceholders);
        $templateContent = $this->templateContent;
        $placeholders = $this->getTemplatePlaceholders($templateContent);
        foreach ($placeholders as $placeholder) {
            if (isset($replacePlaceholders[$placeholder])) {
                $templateContent = str_replace('%%' . $placeholder . '%%', $replacePlaceholders[$placeholder], $templateContent);
            }
        }

        return $templateContent;
    }

    protected function getTemplatePlaceholders($templateContent)
    {
        $regex = '/%%(\S+)%%/';
        preg_match_all($regex, $templateContent, $matches);

        return $matches[1] ?? [];
    }

    protected function calculateNamespaceForPhpClass($filePath): string
    {
        $namespaceConfig = $this->configurationData['namespaceConfig'] ?? null;

        $rootNamespace = $namespaceConfig['rootNamespace'];
        $namespaceCalculateFrom = $namespaceConfig['calculatePathNamespaceCommencingBy'];

        $pathInfo = pathinfo($filePath);
        $path = $pathInfo['dirname'];

        $replacedFilePathWithoutCommencingByNamespace = substr(
            $path,
            (stripos($path, $namespaceCalculateFrom) + strlen($namespaceCalculateFrom))
        );

        return $rootNamespace . str_replace(
                ['/', '\\'],
                '\\',
                $replacedFilePathWithoutCommencingByNamespace
        );
    }

    protected function generateUseDefinitions(array $useDefinitions, $output = ''): string
    {
        foreach ($useDefinitions as $definition) {
            $output .= 'use ' . $definition .';' . PHP_EOL;
        }

        return $output;
    }

    protected function generateClassConstructParams($attributes): string
    {
        $output = [];

        foreach ($attributes as $attribute) {
            $output[] = $this->drawTabSpace(2) . 'private ' . $attribute['type'] . ' $' . $attribute['name'];
        }

        return implode(',' . PHP_EOL, $output);
    }

    protected function generateMethodConstructParams($attributes): string
    {
        $output = [];

        foreach ($attributes as $attribute) {
            $type = !empty(trim($attribute['type'])) ? $attribute['type'] . ' ' : '';
            $output[] = $this->drawTabSpace(2) . $type . '$' . $attribute['name'];
        }

        return implode(',' . PHP_EOL, $output);
    }

    protected function generateClassAttributesGetters(array $classAttributes, $output = ''): string
    {
        foreach ($classAttributes as $attribute) {
            $name = $attribute['name'];
            $type = $attribute['type'];

            $output .= $this->drawTabSpace(1) . 'public function get' . ucfirst($name) . '(): ' . $type . PHP_EOL;
            $output .= $this->drawTabSpace(1) . '{' . PHP_EOL;
            $output .= $this->drawTabSpace(2) . 'return $this->' . $name . ';' . PHP_EOL;
            $output .= $this->drawTabSpace(1) . '}' . PHP_EOL;
            $output .= PHP_EOL;
        }

        return $output;
    }

    protected function generateDomainServicePropertyClassDefinition(BasePhpClassTemplate $domainServiceTemplateClass): string
    {
        return $this->drawTabSpace(1) . 'private ' . $domainServiceTemplateClass->getClassName() . ' $' . lcfirst($domainServiceTemplateClass->getClassName()) . ';';
    }

    protected function getUseStatementForTemplateClass(BasePhpClassTemplate $phpClassTemplate): string
    {
        return $phpClassTemplate->getClassNamespace() . '\\' . $phpClassTemplate->getClassName();
    }

    protected function drawTabSpace(int $repeat): string
    {
        return str_repeat(' ', $repeat * 4);
    }

    protected function getClassNamespace(): string
    {
        return $this->classNamespace;
    }

    protected function getClassName(): string
    {
        return $this->className;
    }

    public static function sortByDependencies($items): array
    {
        $res = [];
        $doneList = [];

        // while not all items are resolved:
        while(count($items) > count($res)) {
            $doneSomething = false;

            foreach($items as $itemIndex => $item) {
                if(isset($doneList[$item['templateClassDispatcher']])) {
                    // item already in resultset
                    continue;
                }
                $resolved = true;

                if(isset($item['dependencies'])) {
                    foreach($item['dependencies'] as $dep) {
                        if(!isset($doneList[$dep])) {
                            // there is a dependency that is not met:
                            $resolved = false;
                            break;
                        }
                    }
                }
                if($resolved) {
                    //all dependencies are met:
                    $doneList[$item['templateClassDispatcher']] = true;
                    $res[] = $item;
                    $doneSomething = true;
                }
            }
            if(!$doneSomething) {
                return [];
            }
        }
        return $res;
    }
}
