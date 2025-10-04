<?php

namespace App\Enum;

enum UserRole: string
{
    case Patient = 'Patient';
    case Dentist = 'Dentist';
    case Admin   = 'Admin';
}
