<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Legacy\Templating\Tests\Twig;

use eZ\Publish\Core\MVC\Legacy\Templating\Twig\Environment;
use eZ\Publish\Core\MVC\Legacy\Templating\LegacyEngine;
use eZ\Publish\Core\MVC\Legacy\Templating\Twig\Template;
use PHPUnit\Framework\TestCase;
use Twig\Error\LoaderError;
use Twig\Loader\LoaderInterface;

class EnvironmentTest extends TestCase
{
    /**
     * @covers \eZ\Publish\Core\MVC\Legacy\Templating\Twig\Environment::loadTemplate
     * @covers \eZ\Publish\Core\MVC\Legacy\Templating\Twig\Template::getTemplateName
     */
    public function testLoadTemplateLegacy()
    {
        $legacyEngine = $this->createMock(LegacyEngine::class);

        $templateName = 'design:test/helloworld.tpl';
        $legacyEngine->expects($this->any())
            ->method('supports')
            ->with($templateName)
            ->will($this->returnValue(true));

        $legacyEngine->expects($this->any())
            ->method('exists')
            ->with($templateName)
            ->will($this->returnValue(true));

        $twigEnv = new Environment($this->createMock(LoaderInterface::class));
        $twigEnv->setEzLegacyEngine($legacyEngine);
        $template = $twigEnv->loadTemplate($templateName);
        $this->assertInstanceOf(Template::class, $template);
        $this->assertSame($templateName, $template->getTemplateName());

        // Calling loadTemplate a 2nd time with the same template name should return the very same Template object.
        $this->assertSame($template, $twigEnv->loadTemplate($templateName));
    }

    /**
     * @covers \eZ\Publish\Core\MVC\Legacy\Templating\Twig\Environment::loadTemplate
     * @covers \eZ\Publish\Core\MVC\Legacy\Templating\Twig\Template::getTemplateName
     */
    public function testLoadNonExistingTemplateLegacy()
    {
        $this->expectException(LoaderError::class);

        $legacyEngine = $this->createMock(LegacyEngine::class);

        $templateName = 'design:test/helloworld.tpl';
        $legacyEngine->expects($this->any())
            ->method('supports')
            ->with($templateName)
            ->will($this->returnValue(true));

        $legacyEngine->expects($this->any())
            ->method('exists')
            ->with($templateName)
            ->will($this->returnValue(false));

        $twigEnv = new Environment($this->createMock(LoaderInterface::class));
        $twigEnv->setEzLegacyEngine($legacyEngine);
        $twigEnv->loadTemplate($templateName);
    }
}
