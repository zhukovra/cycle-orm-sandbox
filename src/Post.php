<?php

declare(strict_types=1);

namespace Example;

use Cycle\Annotated\Annotation\Entity;
use Cycle\Annotated\Annotation\Column;
use Cycle\Annotated\Annotation\Relation;

#[Entity]
class Post
{
    #[Column(type: "integer", primary: true)]
    private int $id;

    #[Relation\HasMany(target: Comment::class)]
    private array $comments = [];

    public function __construct(
        #[Column(type: 'string')]
        private string $text,
    ) {
    }
}
