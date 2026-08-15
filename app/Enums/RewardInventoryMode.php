<?php

namespace App\Enums;

enum RewardInventoryMode: string
{
    case Finite = 'finite';
    case NonInventory = 'non_inventory';
}
