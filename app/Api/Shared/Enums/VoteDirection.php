<?php

declare(strict_types=1);


namespace App\Api\Shared\Enums;


enum VoteDirection: string
{
    case UP = 'up';
    case DOWN = 'down';
}
