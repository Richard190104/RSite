<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class AddTextToGalleries extends BaseMigration
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
        $table = $this->table('galleries');
        // Optional short caption, edited per-photo (see Admin\GalleriesController::edit()) —
        // shown on the public gallery instead of the parent category name (see
        // templates/Gallery/cards.php) when set, and below the enlarged photo in the
        // lightbox. Capped short (see CategoriesTable-style maxLength in GalleriesTable)
        // so it always fits the caption bar without wrapping/overflowing.
        $table->addColumn('text', 'string', [
            'default' => null,
            'limit' => 80,
            'null' => true,
        ]);
        $table->update();
    }
}
