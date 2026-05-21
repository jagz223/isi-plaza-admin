<?php

namespace App\Enums;

enum AccessStatus: string
{
    case Pending = 'pending';
    case Active = 'active';
    case Denied = 'denied';
}
