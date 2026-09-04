<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class AddLocationAndTimeToEvents extends BaseMigration
{
    public function change(): void
    {
        $this->table('events')
            ->addColumn('location', 'string', [
                'default' => null,
                'limit' => 255,
                'null' => true,
                'after' => 'date',
            ])
            ->addColumn('time', 'string', [
                'default' => null,
                'limit' => 32,
                'null' => true,
                'after' => 'location',
            ])
            ->update();
    }
}
