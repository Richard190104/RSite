<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class AddSectionToCommitteeMembers extends BaseMigration
{
    public function change(): void
    {
        $table = $this->table('committee_members');
        $table->addColumn('section', 'string', [
            'default' => '',
            'limit' => 255,
            'null' => false,
            'after' => 'role',
        ]);
        $table->update();
    }
}
