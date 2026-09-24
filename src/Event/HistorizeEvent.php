<?php

declare(strict_types=1);

namespace App\Event;

use App\Entity\Enum\HistoryTypeEnum;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * @author  Agence Dn'D <contact@dnd.fr>
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link    https://www.dnd.fr/
 */
class HistorizeEvent extends Event
{
    public function __construct(
        public readonly string $message,
        public readonly HistoryTypeEnum $type = HistoryTypeEnum::OTHER,
    ) {
    }
}
