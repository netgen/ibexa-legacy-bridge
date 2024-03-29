<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishLegacyBundle\Tests\Cache;

use eZ\Bundle\EzPublishLegacyBundle\Cache\PersistenceCachePurger;
use Ibexa\Contracts\Core\Persistence\Content\Location;
use Ibexa\Contracts\Core\Persistence\Content\Location\Handler;
use Ibexa\Core\Persistence\Cache\Identifier\CacheIdentifierGeneratorInterface;
use Symfony\Component\Cache\Adapter\TagAwareAdapterInterface;
use PHPUnit\Framework\TestCase;

class PersistenceCachePurgerTest extends TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $cacheService;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $locationHandler;

    /**
     * @var \Ibexa\Core\Persistence\Cache\Identifier\CacheIdentifierGeneratorInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $cacheIdentifierGenerator;

    /**
     * @var \eZ\Bundle\EzPublishLegacyBundle\Cache\PersistenceCachePurger
     */
    private $cachePurger;

    protected function setUp()
    {
        parent::setUp();
        $this->cacheService = $this->createMock(TagAwareAdapterInterface::class);
        $this->locationHandler = $this->createMock(Handler::class);
        $this->cacheIdentifierGenerator = $this->createMock(CacheIdentifierGeneratorInterface::class);

        $this->cachePurger = new PersistenceCachePurger(
            $this->cacheService,
            $this->locationHandler,
            $this->cacheIdentifierGenerator
        );
    }

    /**
     * Test case for https://jira.ez.no/browse/EZP-20618.
     */
    public function testNotFoundLocation()
    {
        $id = 'locationIdThatDoesNotExist';
        $this->locationHandler
            ->expects($this->once())
            ->method('loadList')
            ->with([$id])
            ->willReturn([]);

        $this->cachePurger->content($id);
    }

    /**
     * @covers \eZ\Bundle\EzPublishLegacyBundle\Cache\PersistenceCachePurger::all
     * @covers \eZ\Bundle\EzPublishLegacyBundle\Cache\PersistenceCachePurger::isAllCleared
     */
    public function testClearAll()
    {
        $this->cacheService
            ->expects($this->once())
            ->method('clear')
            ->with();

        $this->cachePurger->all();
        $this->assertTrue($this->cachePurger->isAllCleared());
    }

    /**
     * @covers \eZ\Bundle\EzPublishLegacyBundle\Cache\PersistenceCachePurger::all
     * @covers \eZ\Bundle\EzPublishLegacyBundle\Cache\PersistenceCachePurger::resetAllCleared
     * @covers \eZ\Bundle\EzPublishLegacyBundle\Cache\PersistenceCachePurger::isAllCleared
     */
    public function testResetAllCleared()
    {
        $this->assertFalse($this->cachePurger->isAllCleared());
        $this->cachePurger->all();
        $this->assertTrue($this->cachePurger->isAllCleared());
        $this->cachePurger->resetAllCleared();
        $this->assertFalse($this->cachePurger->isAllCleared());
    }

    /**
     * @covers \eZ\Bundle\EzPublishLegacyBundle\Cache\PersistenceCachePurger::all
     * @covers \eZ\Bundle\EzPublishLegacyBundle\Cache\PersistenceCachePurger::content
     */
    public function testClearContentAlreadyCleared()
    {
        $this->cachePurger->all();
        $this->cacheService
            ->expects($this->never())
            ->method('clear');
        $this->cachePurger->content();
    }

    /**
     * @covers \eZ\Bundle\EzPublishLegacyBundle\Cache\PersistenceCachePurger::content
     */
    public function testClearContentDisabled()
    {
        $this->cachePurger->switchOff();
        $this->cacheService
            ->expects($this->never())
            ->method('clear');
        $this->cachePurger->content();
    }

    /**
     * @covers \eZ\Bundle\EzPublishLegacyBundle\Cache\PersistenceCachePurger::setEnabled
     * @covers \eZ\Bundle\EzPublishLegacyBundle\Cache\PersistenceCachePurger::all
     */
    public function testClearAllDisabled()
    {
        $this->cachePurger->switchOff();
        $this->cacheService
            ->expects($this->never())
            ->method('clear');
        $this->cachePurger->all();
    }

    /**
     * @covers \eZ\Bundle\EzPublishLegacyBundle\Cache\PersistenceCachePurger::content
     */
    public function testClearAllContent()
    {
        $this->cacheService
            ->expects($this->once())
            ->method('clear');

        $this->assertNull($this->cachePurger->content());
    }

    /**
     * @covers \eZ\Bundle\EzPublishLegacyBundle\Cache\PersistenceCachePurger::content
     */
    public function testClearContent()
    {
        $locationId1 = 1;
        $contentId1 = 10;
        $locationId2 = 2;
        $contentId2 = 20;
        $locationId3 = 3;
        $contentId3 = 30;

        $locationIds = [$locationId1, $locationId2, $locationId3];

        $this->locationHandler
            ->expects($this->once())
            ->method('loadList')
            ->with($locationIds)
            ->willReturn([
                $locationId1 => $this->buildLocation($locationId1, $contentId1),
                $locationId2 => $this->buildLocation($locationId2, $contentId2),
                $locationId3 => $this->buildLocation($locationId3, $contentId3),
            ]);

        $this->cacheService
            ->expects($this->any())
            ->method('clear')
            ->willReturnMap(
                [
                    ['content', $contentId1, null],
                    ['content', 'info', $contentId1, null],
                    ['content', $contentId2, null],
                    ['content', 'info', $contentId2, null],
                    ['content', $contentId3, null],
                    ['content', 'info', $contentId3, null],
                    ['urlAlias', null],
                    ['location', null],
                ]
            );

        $this->assertSame($locationIds, $this->cachePurger->content($locationIds));
    }

    /**
     * @covers \eZ\Bundle\EzPublishLegacyBundle\Cache\PersistenceCachePurger::content
     */
    public function testClearOneContent()
    {
        $locationId = 1;
        $contentId = 10;

        $this->locationHandler
            ->expects($this->once())
            ->method('loadList')
            ->with([$locationId])
            ->willReturn([$locationId => $this->buildLocation($locationId, $contentId)]);

        $this->cacheService
            ->expects($this->any())
            ->method('clear')
            ->willReturnMap(
                [
                    ['content', $contentId, null],
                    ['content', 'info', $contentId, null],
                    ['content', 'info', 'remoteId', null],
                    ['urlAlias', null],
                    ['location', null],
                ]
            );

        $this->assertSame([$locationId], $this->cachePurger->content($locationId));
    }

    /**
     * @param $locationId
     * @param $contentId
     *
     * @return \Ibexa\Contracts\Core\Persistence\Content\Location
     */
    private function buildLocation($locationId, $contentId)
    {
        return new Location(
            [
                'id' => $locationId,
                'contentId' => $contentId,
            ]
        );
    }

    /**
     * @covers \eZ\Bundle\EzPublishLegacyBundle\Cache\PersistenceCachePurger::content
     */
    public function testClearContentFail()
    {
        $this->expectException(\Ibexa\Core\Base\Exceptions\InvalidArgumentType::class);

        $this->cachePurger->content(new \stdClass());
    }

    /**
     * @covers \eZ\Bundle\EzPublishLegacyBundle\Cache\PersistenceCachePurger::contentType
     */
    public function testClearContentTypeAll()
    {
        $generatorArguments = [['type_map', [], false]];
        $tags = ['tm'];

        $this->cacheIdentifierGenerator
            ->expects($this->exactly(count($tags)))
            ->method('generateTag')
            ->withConsecutive(...$generatorArguments)
            ->willReturnOnConsecutiveCalls(...$tags);

        $this->cacheService
            ->expects($this->once())
            ->method('invalidateTags')
            ->with($tags);

        $this->cachePurger->contentType();
    }

    /**
     * @covers \eZ\Bundle\EzPublishLegacyBundle\Cache\PersistenceCachePurger::contentType
     */
    public function testClearContentType()
    {
        $typeId = 123;
        $generatorArguments = [['type', [$typeId], false]];
        $tags = ['t-' . $typeId];

        $this->cacheIdentifierGenerator
            ->expects($this->exactly(count($tags)))
            ->method('generateTag')
            ->withConsecutive(...$generatorArguments)
            ->willReturnOnConsecutiveCalls(...$tags);

        $this->cacheService
            ->expects($this->once())
            ->method('invalidateTags')
            ->with($tags);

        $this->cachePurger->contentType($typeId);
    }

    /**
     * @covers \eZ\Bundle\EzPublishLegacyBundle\Cache\PersistenceCachePurger::contentType
     */
    public function testClearContentTypeFail()
    {
        $this->expectException(\Ibexa\Core\Base\Exceptions\InvalidArgumentType::class);

        $this->cachePurger->contentType(new \stdClass());
    }

    /**
     * @covers \eZ\Bundle\EzPublishLegacyBundle\Cache\PersistenceCachePurger::contentTypeGroup
     */
    public function testClearContentTypeGroup()
    {
        $typeGroupId = 123;
        $tags = ['tg-' . $typeGroupId, 'tm'];
        $generatorArguments = [
            ['type_group', [$typeGroupId], false],
            ['type_map', [], false],
        ];

        $this->cacheIdentifierGenerator
            ->expects($this->exactly(count($tags)))
            ->method('generateTag')
            ->withConsecutive(...$generatorArguments)
            ->willReturnOnConsecutiveCalls(...$tags);

        $this->cacheService
            ->expects($this->once())
            ->method('invalidateTags')
            ->with($tags);

        $this->cachePurger->contentTypeGroup($typeGroupId);
    }

    /**
     * @covers \eZ\Bundle\EzPublishLegacyBundle\Cache\PersistenceCachePurger::contentTypeGroup
     */
    public function testClearContentTypeGroupFail()
    {
        $this->expectException(\Ibexa\Core\Base\Exceptions\InvalidArgumentType::class);

        $this->cachePurger->contentTypeGroup(new \stdClass());
    }

    /**
     * @covers \eZ\Bundle\EzPublishLegacyBundle\Cache\PersistenceCachePurger::section
     */
    public function testClearSection()
    {
        $sectionId = 123;
        $generatorArguments = [['section', [$sectionId], false]];
        $tags = ['se-' . $sectionId];

        $this->cacheIdentifierGenerator
            ->expects($this->exactly(count($tags)))
            ->method('generateTag')
            ->withConsecutive(...$generatorArguments)
            ->willReturnOnConsecutiveCalls(...$tags);

        $this->cacheService
            ->expects($this->once())
            ->method('invalidateTags')
            ->with($tags);

        $this->cachePurger->section($sectionId);
    }

    /**
     * @covers \eZ\Bundle\EzPublishLegacyBundle\Cache\PersistenceCachePurger::section
     */
    public function testClearSectionFail()
    {
        $this->expectException(\Ibexa\Core\Base\Exceptions\InvalidArgumentType::class);

        $this->cachePurger->section(new \stdClass());
    }

    /**
     * @covers \eZ\Bundle\EzPublishLegacyBundle\Cache\PersistenceCachePurger::languages
     */
    public function testClearLanguages()
    {
        $languageId1 = 123;
        $languageId2 = 456;
        $languageId3 = 789;
        $tags = ['la-' . $languageId1, 'la-' . $languageId2, 'la-' . $languageId3];
        $generatorArguments = [
            ['language', [$languageId1], false],
            ['language', [$languageId2], false],
            ['language', [$languageId3], false],
        ];

        $this->cacheIdentifierGenerator
            ->expects($this->exactly(count($tags)))
            ->method('generateTag')
            ->withConsecutive(...$generatorArguments)
            ->willReturnOnConsecutiveCalls(...$tags);

        $this->cacheService
            ->expects($this->once())
            ->method('invalidateTags')
            ->with($tags);

        $this->cachePurger->languages([$languageId1, $languageId2, $languageId3]);
    }

    /**
     * @covers \eZ\Bundle\EzPublishLegacyBundle\Cache\PersistenceCachePurger::languages
     */
    public function testClearOneLanguage()
    {
        $languageId = 123;
        $tags = ['la-' . $languageId];
        $generatorArguments = [['language', [$languageId], false]];

        $this->cacheIdentifierGenerator
            ->expects($this->exactly(count($tags)))
            ->method('generateTag')
            ->withConsecutive(...$generatorArguments)
            ->willReturnOnConsecutiveCalls(...$tags);

        $this->cacheService
            ->expects($this->once())
            ->method('invalidateTags')
            ->with($tags);

        $this->cachePurger->languages($languageId);
    }

    /**
     * @covers \eZ\Bundle\EzPublishLegacyBundle\Cache\PersistenceCachePurger::user
     */
    public function testClearUserAll()
    {
        $this->markTestSkipped('Enable when clearing all user cache is implemented.');

        $this->cacheService
            ->expects($this->once())
            ->method('clear');

        $this->cachePurger->user();
    }

    /**
     * @covers \eZ\Bundle\EzPublishLegacyBundle\Cache\PersistenceCachePurger::user
     */
    public function testClearUser()
    {
        $userId = 123;
        $generatorArguments = [['user', [$userId], false]];
        $tags = ['u-123'];

        $this->cacheIdentifierGenerator
            ->expects($this->exactly(count($tags)))
            ->method('generateTag')
            ->withConsecutive(...$generatorArguments)
            ->willReturnOnConsecutiveCalls(...$tags);

        $this->cacheService
            ->expects($this->once())
            ->method('invalidateTags')
            ->with($tags);

        $this->cachePurger->user($userId);
    }

    /**
     * @covers \eZ\Bundle\EzPublishLegacyBundle\Cache\PersistenceCachePurger::user
     */
    public function testClearUserFail()
    {
        $this->expectException(\Ibexa\Core\Base\Exceptions\InvalidArgumentType::class);

        $this->cachePurger->user(new \stdClass());
    }

    /**
     * @covers \eZ\Bundle\EzPublishLegacyBundle\Cache\PersistenceCachePurger::contentVersion
     * @dataProvider getDataForTestClearVersionForOneContent
     */
    public function testClearVersionOfOneContent($contentId, $versionNo)
    {
        $keysToBeDeleted = ["ibx-cvi-${contentId}-${versionNo}", "ibx-c-${contentId}-vl"];
        $tagsToBeInvalidated = ["c-${contentId}-vl", "c-${contentId}-v-${versionNo}"];
        $keyGeneratorArguments = [
            ['content_version_info', [$contentId], true],
            ['content_version_list', [$contentId], true],
        ];
        $tagGeneratorArguments = [
            ['content_version_list', [$contentId], false],
            ['content_version', [$contentId, $versionNo], false],
        ];

        $this->cacheIdentifierGenerator
            ->expects($this->exactly(count($keysToBeDeleted)))
            ->method('generateKey')
            ->withConsecutive(...$keyGeneratorArguments)
            ->willReturnOnConsecutiveCalls(...$keysToBeDeleted);

        $this->cacheService
            ->expects($this->once())
            ->method('deleteItems')
            ->with($keysToBeDeleted);

        $this->cacheIdentifierGenerator
            ->expects($this->exactly(count($tagsToBeInvalidated)))
            ->method('generateTag')
            ->withConsecutive(...$tagGeneratorArguments)
            ->willReturnOnConsecutiveCalls(...$tagsToBeInvalidated);

        $this->cacheService
            ->expects($this->once())
            ->method('invalidateTags')
            ->with($tagsToBeInvalidated);

        $this->cachePurger->contentVersion($contentId, $versionNo);
    }

    public function getDataForTestClearVersionForOneContent()
    {
        return [
            [18, 37],
        ];
    }
}
