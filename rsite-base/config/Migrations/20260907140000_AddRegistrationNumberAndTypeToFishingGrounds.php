<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class AddRegistrationNumberAndTypeToFishingGrounds extends BaseMigration
{
    public function change(): void
    {
        $table = $this->table('fishing_grounds');
        $table->addColumn('registration_number', 'string', [
            'default' => null,
            'limit' => 255,
            'null' => true,
            'after' => 'title',
        ]);
        $table->addColumn('type', 'string', [
            'default' => null,
            'limit' => 255,
            'null' => true,
            'after' => 'registration_number',
        ]);
        $table->update();
    }
}
