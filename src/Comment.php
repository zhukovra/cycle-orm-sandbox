<?php

declare(strict_types=1);

namespace Example;

use Cycle\Annotated\Annotation\Entity;
use Cycle\Annotated\Annotation\Column;
use Cycle\Annotated\Annotation\Relation;

#[Entity]
class Comment
{
    #[Column(type: 'integer', primary: true)]
    private int $id;

    #[Relation\BelongsTo(target: Post::class)]
    private Post $post;

    #[Relation\RefersTo(target: Comment::class, nullable: true)]
    private ?Comment $parent;

    public function __construct(Post $post, ?Comment $comment = null)
    {
        $this->post = $post;
        $this->parent = $comment;
    }
}
