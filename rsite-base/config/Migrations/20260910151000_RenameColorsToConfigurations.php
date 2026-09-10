<?php
declare(strict_types=1);

use Migrations\BaseMigration;

/**
 * The 'colors' table becomes 'configurations' — it's no longer only colors,
 * it's every site-wide admin setting saved from the "Configurations" admin
 * screen (see Admin\ConfigurationsController, itself already renamed from
 * ColorsController). A rename, not drop+recreate, since this table already
 * holds admin-customized values in production (see CreateColors/
 * AddReviryMapBgColor) that a fresh table would silently lose on deploy.
 *
 * Also adds the first non-color row: 'automatic_images', a '1'/'0' flag
 * read by ConfigurationsTable::automaticImagesEnabled() — whether a record
 * with no image of its own gets a random stock photo (PlaceholderImagesTable
 * ::random()) or a plain "no image" graphic. Defaults to '1' (enabled) so
 * existing sites keep their current behaviour until an admin turns it off.
 */
class RenameColorsToConfigurations extends BaseMigration
{
    public function up(): void
    {
        $this->table('colors')->rename('configurations')->update();

        $now = date('Y-m-d H:i:s');
        $this->table('configurations')->insert([
            ['slug' => 'automatic_images', 'value' => '1', 'created' => $now, 'modified' => $now],
        ])->saveData();
    }

    public function down(): void
    {
        $this->execute("DELETE FROM configurations WHERE slug = 'automatic_images'");
        $this->table('configurations')->rename('colors')->update();
    }
}
