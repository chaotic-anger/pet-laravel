<?php

declare(strict_types=1);


namespace App\Http\Resources;


use App\Enums\VoteDirection;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property VoteDirection $direction
 */
class CommentVoteResource extends JsonResource
{
    public function toArray(Request $request)
    {
        return [
            'direction' => $this->direction->value
        ];
    }
}
