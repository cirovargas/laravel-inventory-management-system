<?php

namespace App\Domain\Sales\Enum;

enum SaleStatus: string
{
    case PENDING = 'pending';
    case PROCESSING = 'processing';
    case COMPLETED = 'completed';
    case FAILED = 'failed';

}
