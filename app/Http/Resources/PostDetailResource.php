<?php

namespace App\Http\Resources;

use App\Models\Comment;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $title
 * @property string $content
 * @property Carbon $created_at
 * @method \Illuminate\Database\Eloquent\Relations\HasMany<Comment> comments()
 */
class PostDetailResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'content' => $this->content,
            'created_at' => $this->created_at->toDateTimeString(),
            'comments' => CommentResource::collection(
                $this->whenLoaded('comments')->orderByDesc('rating')->get()
            )
        ];
    }
}
