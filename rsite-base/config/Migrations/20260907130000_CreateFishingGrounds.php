<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class CreateFishingGrounds extends BaseMigration
{
    public function change(): void
    {
        $table = $this->table('fishing_grounds');
        $table->addColumn('title', 'string', [
            'default' => null,
            'limit' => 255,
            'null' => false,
        ]);
        $table->addColumn('description', 'text', [
            'default' => null,
            'null' => true,
        ]);
        $table->addColumn('image', 'string', [
            'default' => null,
            'limit' => 255,
            'null' => true,
        ]);
        $table->addColumn('location', 'string', [
            'default' => null,
            'limit' => 255,
            'null' => true,
        ]);
        $table->addColumn('latitude', 'decimal', [
            'default' => null,
            'precision' => 10,
            'scale' => 7,
            'null' => true,
        ]);
        $table->addColumn('longitude', 'decimal', [
            'default' => null,
            'precision' => 10,
            'scale' => 7,
            'null' => true,
        ]);
        $table->addColumn('created', 'datetime', [
            'default' => null,
            'null' => false,
        ]);
        $table->addColumn('modified', 'datetime', [
            'default' => null,
            'null' => false,
        ]);
        $table->create();
    }
}
