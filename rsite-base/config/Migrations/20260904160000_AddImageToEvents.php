<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class AddImageToEvents extends BaseMigration
{
    public function change(): void
    {
        $this->table('events')
            ->addColumn('image', 'string', [
                'default' => null,
                'limit' => 255,
                'null' => true,
                'after' => 'time',
            ])
            ->update();
    }
}
