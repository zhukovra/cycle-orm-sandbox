<?php

declare(strict_types=1);

include 'vendor/autoload.php';

use Cycle\Database;
use Cycle\Database\Config;
use Cycle\ORM;
use Cycle\Schema;

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

$dbal->database()->execute('DROP TABLE IF EXISTS users');

$registry = new Schema\Registry($dbal);
$entity = new Schema\Definition\Entity();

$entity
    ->setRole('user')
    ->setClass(User::class);

$entity->getFields()
    // uncomment next line and run script again
    // ->set('text', (new Schema\Definition\Field())->setType('string')->setColumn('text'))
    ->set('id', (new Schema\Definition\Field())->setType('primary')->setColumn('id')->setPrimary(true));

$registry->register($entity);

$registry->linkTable($entity, 'default', 'users');


$schema = (new Schema\Compiler())->compile($registry, [
    new Schema\Generator\RenderTables(),
    new Schema\Generator\SyncTables(),
]);

$orm = new ORM\ORM(new ORM\Factory($dbal), new ORM\Schema($schema));
$em = new ORM\EntityManager($orm);

$em->persist(new User('1'))->run();
echo 'ROWS COUNT: ' . count($dbal->database('default')->query('SELECT * FROM users')->fetchAll());

class User
{
    public function __construct(
        public string $text = '',
    ) {
    }

}