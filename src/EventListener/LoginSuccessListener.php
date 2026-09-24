<?php

declare(strict_types=1);

namespace App\EventListener;

use App\Entity\Enum\HistoryTypeEnum;
use App\Event\HistorizeEvent;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

#[AsEventListener]
final class LoginSuccessListener
{
    public function __construct(
        private readonly EventDispatcherInterface $eventDispatcher,
    ) {
    }

    public function __invoke(LoginSuccessEvent $event): void
    {
        $this->eventDispatcher->dispatch(new HistorizeEvent(
            \sprintf('Connexion de %s', $event->getUser()->getUserIdentifier()),
            HistoryTypeEnum::LOGIN,
        ));
    }
}
