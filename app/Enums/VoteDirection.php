<?php

declare(strict_types=1);


namespace App\Enums;


enum VoteDirection: string
{
    case UP = 'up';
    case DOWN = 'down';
}
