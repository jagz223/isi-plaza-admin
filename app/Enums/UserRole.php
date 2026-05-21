<?php

namespace App\Enums;

enum UserRole: string
{
    case Comprador = 'comprador';
    case Mayorista = 'mayorista';
    case Admin = 'admin';
}
