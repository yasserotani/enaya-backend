<?php

namespace App\Enums;

enum AppointmentStatus: string
{
    case Scheduled = 'scheduled';
    case Confirmed = 'confirmed';
    case Arrived = 'arrived';
    case InProgress = 'inProgress';
    case Completed = 'completed';
    case Cancelled = 'cancelled';
    case NoShow = 'noShow';
    case Rescheduled = 'rescheduled';
}
