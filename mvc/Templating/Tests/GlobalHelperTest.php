<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Legacy\Templating\Tests;

use eZ\Publish\Core\MVC\Legacy\Templating\GlobalHelper;
use Ibexa\Core\MVC\Symfony\Templating\Tests\GlobalHelperTest as BaseGlobalHelperTest;
use eZ\Publish\Core\MVC\Legacy\Templating\LegacyHelper;

class GlobalHelperTest extends BaseGlobalHelperTest
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $legacyHelper;

    protected function setUp()
    {
        parent::setUp();
        $this->legacyHelper = $this->getMockBuilder(LegacyHelper::class)
            ->setConstructorArgs([
                static function () {
                },
            ])
            ->setMethods([])
            ->getMock();

        // Force to use Legacy GlobalHelper
        $this->helper = new GlobalHelper($this->configResolver, $this->locationService, $this->router, $this->translationHelper);
        $this->helper->setLegacyHelper($this->legacyHelper);
    }

    public function testGetLegacy()
    {
        $this->assertSame($this->legacyHelper, $this->helper->getLegacy());
    }
}
