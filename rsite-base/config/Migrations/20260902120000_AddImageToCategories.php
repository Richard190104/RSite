<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class AddImageToCategories extends BaseMigration
{
    /**
     * Change Method.
     *
     * More information on this method is available here:
     * https://book.cakephp.org/migrations/5/guides/writing-migrations/migration-methods.html#the-change-method
     *
     * @return void
     */
    public function change(): void
    {
        $table = $this->table('categories');
        // Optional — the public gallery falls back to a placeholder card
        // background when a category has none set (see
        // templates/Gallery/cards.php).
        $table->addColumn('image', 'string', [
            'default' => null,
            'limit' => 255,
            'null' => true,
        ]);
        $table->update();
    }
}
