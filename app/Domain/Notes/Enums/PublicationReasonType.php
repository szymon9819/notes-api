<?php

declare(strict_types=1);

namespace App\Domain\Notes\Enums;

enum PublicationReasonType: string
{
    case Decision = 'decision';
    case Reminder = 'reminder';
    case Knowledge = 'knowledge';
    case Announcement = 'announcement';
}
