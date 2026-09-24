<?php

declare(strict_types=1);

namespace App\EventListener;

use App\Entity\Enum\HistoryTypeEnum;
use App\Entity\Enum\LocationStatusEnum;
use App\Entity\Location;
use App\Event\HistorizeEvent;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * Doctrine fires preUpdate mid-flush, before the surrounding flush() call
 * returns — dispatching HistorizeEvent there would make HistorizeEventListener
 * call flush() again while one is already in progress. Pending events are
 * collected in preUpdate and only dispatched from postFlush, once the
 * original flush has fully completed and a new one is safe to trigger.
 */
#[AsEntityListener(event: Events::preUpdate, method: 'preUpdate', entity: Location::class)]
#[AsDoctrineListener(event: Events::postFlush)]
final class LocationStatusChangeListener
{
    /** @var list<HistorizeEvent> */
    private array $pendingEvents = [];

    public function __construct(
        private readonly EventDispatcherInterface $eventDispatcher,
    ) {
    }

    public function preUpdate(Location $location, PreUpdateEventArgs $event): void
    {
        if (!$event->hasChangedField('status')) {
            return;
        }

        $oldStatus = LocationStatusEnum::from($event->getOldValue('status'));
        $newStatus = LocationStatusEnum::from($event->getNewValue('status'));

        $this->pendingEvents[] = new HistorizeEvent(
            \sprintf(
                'Statut de la location %s changé de %s à %s',
                $location->uuid,
                $oldStatus->trans(),
                $newStatus->trans(),
            ),
            HistoryTypeEnum::LOCATION_STATUS_CHANGED,
        );
    }

    public function postFlush(PostFlushEventArgs $args): void
    {
        if ([] === $this->pendingEvents) {
            return;
        }

        $events = $this->pendingEvents;
        $this->pendingEvents = [];

        foreach ($events as $event) {
            $this->eventDispatcher->dispatch($event);
        }
    }
}
