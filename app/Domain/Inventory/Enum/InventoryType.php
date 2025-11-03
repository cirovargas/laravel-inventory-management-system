<?php

namespace App\Domain\Inventory\Enum;

enum InventoryType: string
{
    case ENTRY = 'entry';
    case EXIT = 'exit';
}
