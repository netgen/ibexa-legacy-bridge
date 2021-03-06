<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Legacy\Templating\Twig;

use Twig\Environment;
use Twig\Source;
use Twig\Template as BaseTemplate;
use eZ\Publish\Core\MVC\Legacy\Templating\LegacyEngine;

/**
 * Twig Template class representation for a legacy template.
 */
class Template extends BaseTemplate
{
    private $templateName;

    /**
     * @var \eZ\Publish\Core\MVC\Legacy\Templating\LegacyEngine
     */
    private $legacyEngine;

    public function __construct($templateName, Environment $env, LegacyEngine $legacyEngine)
    {
        parent::__construct($env);

        $this->templateName = $templateName;
        $this->legacyEngine = $legacyEngine;
    }

    /**
     * Renders the template with the given context and returns it as string.
     *
     * @param array $context An array of parameters to pass to the template
     *
     * @return string The rendered template
     */
    public function render(array $context)
    {
        return $this->legacyEngine->render($this->templateName, $context);
    }

    /**
     * Displays the template with the given context.
     *
     * @param array $context An array of parameters to pass to the template
     * @param array $blocks  An array of blocks to pass to the template
     */
    public function display(array $context, array $blocks = [])
    {
        echo $this->render($context);
    }

    /**
     * @return string
     */
    public function getTemplateName()
    {
        return $this->templateName;
    }

    /**
     * {@inheritdoc}
     */
    public function getDebugInfo()
    {
        return [];
    }

    public function getSourceContext()
    {
        return new Source('', '');
    }

    /**
     * {@inheritdoc}
     */
    protected function doDisplay(array $context, array $blocks = [])
    {
    }
}
