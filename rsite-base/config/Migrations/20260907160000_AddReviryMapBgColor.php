<?php
declare(strict_types=1);

use Migrations\BaseMigration;

/**
 * Adds the 'reviry_map_bg' slug to the colors table (see CreateColors and
 * Admin\ColorsController) — the fishing-grounds map's paper-colored SVG
 * background (resources/scss/_reviry.scss's .p-reviry-map__bg), previously
 * a hardcoded fill with no admin control. Same #f4ecd8 value it already had,
 * so this migration only makes the existing color editable — it does not
 * change the map's appearance.
 */
class AddReviryMapBgColor extends BaseMigration
{
    public function up(): void
    {
        $now = date('Y-m-d H:i:s');
        $this->table('colors')->insert([
            ['slug' => 'reviry_map_bg', 'value' => '#f4ecd8', 'created' => $now, 'modified' => $now],
        ])->saveData();
    }

    public function down(): void
    {
        $this->execute("DELETE FROM colors WHERE slug = 'reviry_map_bg'");
    }
}
