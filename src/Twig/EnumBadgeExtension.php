<?php

declare(strict_types=1);

namespace App\Twig;

use App\Entity\Enum\BadgeableEnumInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class EnumBadgeExtension extends AbstractExtension
{
    public function __construct(
        private readonly TranslatorInterface $translator,
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('status_badge', $this->renderBadge(...), ['is_safe' => ['html']]),
        ];
    }

    public function renderBadge(BadgeableEnumInterface $status): string
    {
        return \sprintf(
            '<span class="badge text-bg-%s">%s</span>',
            htmlspecialchars($status->badgeColor(), \ENT_QUOTES),
            htmlspecialchars($this->translator->trans($status->trans()), \ENT_QUOTES),
        );
    }
}
