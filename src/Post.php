<?php

declare(strict_types=1);

namespace Example;

use Cycle\Annotated\Annotation\Entity;
use Cycle\Annotated\Annotation\Column;
use Cycle\Annotated\Annotation\Relation;
use Cycle\ORM\Entity\Behavior\Uuid\Uuid4;
use Ramsey\Uuid\UuidInterface;

#[Entity]
#[Uuid4]
class Post
{
    #[Column(type: "uuid", primary: true)]
    private UuidInterface $uuid;

    #[Relation\HasMany(target: Comment::class)]
    private array $comments = [];

    public function __construct(
        #[Column(type: 'string')]
        private string $text,
    ) {
    }
}
