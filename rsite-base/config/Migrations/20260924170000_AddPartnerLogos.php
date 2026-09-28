<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class AddPartnerLogos extends BaseMigration
{
    /**
     * Homepage partner strip (templates/Pages/home.php). Same fixed-slot
     * pattern as "Main logo": the admin replaces the image, it does not
     * create rows. Empty path stays hidden on the public page.
     *
     * @return void
     */
    public function change(): void
    {
        $now = date('Y-m-d H:i:s');

        $this->table('logos')->insert([
            ['name' => 'Partner — local organisation', 'path' => '', 'created' => $now, 'modified' => $now],
            ['name' => 'Partner — Slovak Fishing Association', 'path' => '', 'created' => $now, 'modified' => $now],
            ['name' => 'Partner — city', 'path' => '', 'created' => $now, 'modified' => $now],
        ])->saveData();
    }
}
