<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class AddDefaultLogos extends BaseMigration
{
    /**
     * The admin UI can't create logo rows (only replace the image behind an
     * existing one), so the slots the templates ask for are seeded here.
     * 'path' starts empty — the admin uploads the actual file.
     *
     * @return void
     */
    public function change(): void
    {
        $now = date('Y-m-d H:i:s');

        $this->table('logos')->insert([
            ['name' => 'Main logo', 'path' => '', 'created' => $now, 'modified' => $now]
        ])->saveData();
    }
}