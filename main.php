<?php

declare(strict_types=1);

include 'vendor/autoload.php';

use Cycle\Annotated;
use Cycle\Database;
use Cycle\Database\Config;
use Cycle\ORM;
use Cycle\ORM\Entity\Behavior\EventDrivenCommandGenerator;
use Cycle\Schema;
use Doctrine\Common\Annotations\AnnotationRegistry;
use \Example as Models;
use Spiral\Core\Container;

$dbal = new Database\DatabaseManager(
    new Config\DatabaseConfig([
        'default' => 'default',
        'databases' => [
            'default' => ['connection' => 'sqlite']
        ],
        'connections' => [
            'sqlite' => new Config\SQLiteDriverConfig(
                connection: new Config\SQLite\FileConnectionConfig(__DIR__ . '/test.db'),
            ),
        ]
    ])
);

$dbal->database()->execute('DROP TABLE IF EXISTS posts');
$dbal->database()->execute('DROP TABLE IF EXISTS comments');

$registry = new Schema\Registry($dbal);

// autoload annotations
AnnotationRegistry::registerLoader('class_exists');

$finder = (new \Symfony\Component\Finder\Finder())->files()->in([__DIR__ . '/src']);
$classLocator = new \Spiral\Tokenizer\ClassLocator($finder);

$schema = (new Schema\Compiler())->compile(new Schema\Registry($dbal), [
    new Schema\Generator\ResetTables(),             // re-declared table schemas (remove columns)
    new Annotated\Embeddings($classLocator),        // register embeddable entities
    new Annotated\Entities($classLocator),          // register annotated entities
    new Annotated\TableInheritance(),               // register STI/JTI
    new Annotated\MergeColumns(),                   // add @Table column declarations
    new Schema\Generator\GenerateRelations(),       // generate entity relations
    new Schema\Generator\GenerateModifiers(),       // generate changes from schema modifiers
    new Schema\Generator\ValidateEntities(),        // make sure all entity schemas are correct
    new Schema\Generator\RenderTables(),            // declare table schemas
    new Schema\Generator\RenderRelations(),         // declare relation keys and indexes
    new Schema\Generator\RenderModifiers(),         // render all schema modifiers
    new Annotated\MergeIndexes(),                   // add @Table column declarations
    new Schema\Generator\SyncTables(),              // sync table changes to database
    new Schema\Generator\GenerateTypecast(),        // typecast non string columns
]);

$db = $dbal->database('default');

$container = new Container();
$schema = new ORM\Schema($schema);

$commandGenerator = new EventDrivenCommandGenerator($schema, $container);
$orm = new ORM\ORM(new ORM\Factory($dbal), $schema, $commandGenerator);
$em = new ORM\EntityManager($orm);

$post = new Models\Post("Post title");
$em->persist($post)->run();
printf("POSTS: %s\n", json_encode($db->query('SELECT * FROM posts')->fetchAll()));

$comment = new Models\Comment($post);
$em->persist($comment)->run();
printf("COMMENTS: %s\n", json_encode($db->query('SELECT * FROM comments')->fetchAll()));

/** @var Models\Post $post */
$post = $orm->getRepository(Models\Post::class)->findOne();
/** @var Models\Comment $comment */
$comment = $orm->getRepository(Models\Comment::class)->findOne();

printf("Founded models with ORM without exceptions\n");

$childComment = new Models\Comment($post, $comment);
$em->persist($childComment)->run();
printf("COMMENTS: %s\n", json_encode($db->query('SELECT * FROM comments')->fetchAll()));