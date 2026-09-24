<?php

declare(strict_types=1);

namespace App\Tests\EventListener;

use App\Entity\Enum\HistoryTypeEnum;
use App\Event\HistorizeEvent;
use App\Repository\HistoryRepository;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class HistorizeEventListenerTest extends KernelTestCase
{
    protected function setUp(): void
    {
        self::bootKernel();
    }

    public function testDispatchingTheEventPersistsAHistoryEntry(): void
    {
        static::getContainer()->get(EventDispatcherInterface::class)
            ->dispatch(new HistorizeEvent('Un test a été exécuté'));

        $history = static::getContainer()->get(HistoryRepository::class)->findOneBy(['message' => 'Un test a été exécuté']);

        self::assertNotNull($history);
        self::assertSame(HistoryTypeEnum::OTHER, $history->type);
        self::assertNull($history->author);
    }
}
