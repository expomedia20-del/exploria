<?php

namespace App\Enums;

enum UserRole: string
{
    case Admin = 'admin';
    case RegionalAdmin = 'regional_admin';
    case Operator = 'operator';
    case Viewer = 'viewer';
    case Visitor = 'visitor';
    case ShopPartner = 'shop_partner';
    case HubManager = 'hub_manager';
    case Sponsor = 'sponsor';
}
