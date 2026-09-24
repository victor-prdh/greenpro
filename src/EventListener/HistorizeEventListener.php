<?php

declare(strict_types=1);

namespace App\EventListener;

use App\Entity\History;
use App\Entity\User;
use App\Event\HistorizeEvent;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener]
final class HistorizeEventListener
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly Security $security,
    ) {
    }

    public function __invoke(HistorizeEvent $event): void
    {
        $user = $this->security->getUser();

        $history = new History();
        $history->message = $event->message;
        $history->type = $event->type;
        $history->author = $user instanceof User ? $user : null;

        $this->entityManager->persist($history);
        $this->entityManager->flush();
    }
}
