<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class AddIsEnabledToBanners extends BaseMigration
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
        $table = $this->table('banners');
        $table->addColumn('is_enabled', 'boolean', [
            'default' => true,
            'null' => false,
        ]);
        // Free-form per-banner extra settings (e.g. text alignment, link URL,
        // button label...) that don't need their own dedicated column yet.
        $table->addColumn('settings', 'json', [
            'default' => null,
            'null' => true,
        ]);
        $table->update();
    }
}