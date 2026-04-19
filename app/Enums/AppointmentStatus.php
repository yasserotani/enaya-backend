<?php

namespace App\Enums;

enum AppointmentStatus: string
{
    case CONFIRMED = 'confirmed';
    case COMPLETED = 'completed';
    case CANCELED = 'canceled';
    case NO_SHOW = 'no_show';
}
