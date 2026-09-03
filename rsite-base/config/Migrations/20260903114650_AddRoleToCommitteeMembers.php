<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class AddRoleToCommitteeMembers extends BaseMigration
{
    public function change(): void
    {
        $table = $this->table('committee_members');
        $table->addColumn('role', 'string', [
            'default' => null,
            'limit' => 255,
            'null' => true,
            'after' => 'name',
        ]);
        $table->update();
    }
}
