<?php

/**
 * This file is part of the ApiGen (http://apigen.org)
 *
 * For the full copyright and license information, please view
 * the file LICENSE that was distributed with this source code.
 */

namespace ApiGen\Generator\TemplateGenerators\Loaders;

use ApiGen\Contracts\Parser\Reflection\ElementReflectionInterface;
use ApiGen\Parser\Elements\Elements;
use ApiGen\Parser\Elements\ElementStorage;
use ApiGen\Templating\Template;

class NamespaceAndPackageLoader
{

    /**
     * @var ElementStorage
     */
    private $elementStorage;


    public function __construct(ElementStorage $elementStorage)
    {
        $this->elementStorage = $elementStorage;
    }


    /**
     * @return Template
     */
    public function loadTemplateWithElementNamespaceOrPackage(Template $template, ElementReflectionInterface $element)
    {
        if ($namespaces = $this->elementStorage->getNamespaces()) {
            $name = $element->getPseudoNamespaceName();
            $template = $this->loadTemplateWithNamespace($template, $name, $namespaces[$name]);

        } elseif ($packages = $this->elementStorage->getPackages()) {
            $name = $element->getPseudoPackageName();
            $template = $this->loadTemplateWithNamespace($template, $name, $packages[$name]);
        }

        return $template;
    }


    /**
     * @param Template $template
     * @param string $name
     * @param array $namespace
     * @return Template
     */
    public function loadTemplateWithNamespace(Template $template, $name, $namespace)
    {
        $template->setParameters([
            'package' => null,
            'namespace' => $name,
            'subnamespaces' => $this->getSubnamesForName($name, $template->getParameters()['namespaces'])
        ]);
        $template = $this->loadTemplateWithElements($template, $namespace);
        return $template;
    }


    /**
     * @param Template $template
     * @param string $name
     * @param array $package
     * @return Template
     */
    public function loadTemplateWithPackage(Template $template, $name, $package)
    {
        $template->setParameters([
            'namespace' => null,
            'package' => $name,
            'subpackages' => $this->getSubnamesForName($name, $template->getParameters()['packages'])
        ]);
        $template = $this->loadTemplateWithElements($template, $package);
        return $template;
    }


    /**
     * @param Template $template
     * @param array $elements
     * @return Template
     */
    private function loadTemplateWithElements(Template $template, $elements)
    {
        return $template->setParameters([
            Elements::CLASSES => $elements[Elements::CLASSES],
            Elements::INTERFACES => $elements[Elements::INTERFACES],
            Elements::TRAITS => $elements[Elements::TRAITS],
            Elements::EXCEPTIONS => $elements[Elements::EXCEPTIONS],
            Elements::CONSTANTS => $elements[Elements::CONSTANTS],
            Elements::FUNCTIONS => $elements[Elements::FUNCTIONS]
        ]);
    }


    /**
     * @param string $name
     * @return array
     */
    private function getSubnamesForName($name, $elements)
    {
        return array_filter($elements, function ($subname) use ($name) {
            $pattern = '~^' . preg_quote($name) . '\\\\[^\\\\]+$~';
            return (bool) preg_match($pattern, $subname);
        });
    }
}
