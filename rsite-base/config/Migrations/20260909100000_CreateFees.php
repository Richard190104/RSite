<?php
declare(strict_types=1);

use Migrations\BaseMigration;

/**
 * A single price-list row shown on the public "poplatky" page, grouped by
 * category there. `price` is free text (not a decimal column) since real
 * entries look like "35 €, 5 €" or "12 € + permit 15 €", not clean numbers.
 * `category` is a string key into the fixed list in FeesTable::CATEGORIES
 * (not a foreign key — poplatky categories are a fixed set, unlike the
 * shared Categories table News/Events/Galleries use). `position` orders
 * rows within a category, set via the admin's drag-and-drop reorder.
 */
class CreateFees extends BaseMigration
{
    public function change(): void
    {
        $table = $this->table('fees');
        $table->addColumn('title', 'string', [
            'default' => null,
            'limit' => 255,
            'null' => false,
        ]);
        $table->addColumn('price', 'string', [
            'default' => null,
            'limit' => 255,
            'null' => false,
        ]);
        $table->addColumn('category', 'string', [
            'default' => null,
            'limit' => 64,
            'null' => false,
        ]);
        $table->addColumn('position', 'integer', [
            'default' => 0,
            'null' => false,
        ]);
        $table->addColumn('created', 'datetime', [
            'default' => null,
            'null' => false,
        ]);
        $table->addColumn('modified', 'datetime', [
            'default' => null,
            'null' => false,
        ]);
        $table->addIndex(['category']);
        $table->create();
    }
}
