<?php
declare(strict_types=1);

use Migrations\BaseMigration;

/**
 * Social profile URL slots for the contact page. Admin edits values via
 * Texts — templates never invent Facebook/Instagram links themselves.
 */
class AddSocialUrlsToTexts extends BaseMigration
{
    public function change(): void
    {
        $now = date('Y-m-d H:i:s');

        $this->table('texts')->insert([
            ['slug' => 'Facebook URL', 'value' => null, 'created' => $now, 'modified' => $now],
            ['slug' => 'Instagram URL', 'value' => null, 'created' => $now, 'modified' => $now],
        ])->saveData();
    }
}
