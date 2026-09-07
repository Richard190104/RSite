<?php
declare(strict_types=1);

use Migrations\BaseMigration;

/**
 * A pool of nature/fishing photos (webroot/img/placeholders/) shown instead
 * of the old plain CSS gradient whenever a News article, Event,
 * FishingGround, or gallery Category has no uploaded image of its own — see
 * PlaceholderImagesTable::random(), called from the templates that used to
 * render a '--placeholder' gradient div. Rows are filenames only, seeded
 * once here; new photos are added via a migration, same convention as
 * `texts`/`colors' — there is no admin UI for this table.
 */
class CreatePlaceholderImages extends BaseMigration
{
    public function up(): void
    {
        $table = $this->table('placeholder_images');
        $table->addColumn('filename', 'string', [
            'default' => null,
            'limit' => 255,
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
        $table->create();

        $now = date('Y-m-d H:i:s');
        $rows = [];
        for ($i = 1; $i <= 32; $i++) {
            $rows[] = [
                'filename' => sprintf('placeholder-%02d.jpg', $i),
                'created' => $now,
                'modified' => $now,
            ];
        }
        $table->insert($rows)->saveData();
    }

    public function down(): void
    {
        $this->table('placeholder_images')->drop()->save();
    }
}
