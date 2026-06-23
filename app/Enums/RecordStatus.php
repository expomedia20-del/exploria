<?php

namespace App\Enums;

enum RecordStatus: string
{
    case Draft = 'draft';
    case Active = 'active';
    case Inactive = 'inactive';
    case Placeholder = 'placeholder';
}
