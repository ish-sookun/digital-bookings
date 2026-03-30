<?php

namespace App;

enum UserRole: string
{
    case SuperAdmin = 'super_admin';
    case Admin = 'admin';
    case Salesperson = 'salesperson';
}
