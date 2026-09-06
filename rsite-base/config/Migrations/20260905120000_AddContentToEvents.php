<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class AddContentToEvents extends BaseMigration
{
    /**
     * Change Method.
     *
     * More information on this method is available here:
     * https://book.cakephp.org/migrations/5/guides/writing-migrations/migration-methods.html#the-change-method
     *
     * @return void
     */
    public function change(): void
    {
        $table = $this->table('events');
        // Optional HTML "poster" field, same feature as News::content — an
        // AI-generated notice-board-style graphic rendered from the
        // event's own title/description (see Admin\AssistantController).
        $table->addColumn('content', 'text', [
            'default' => null,
            'null' => true,
            'after' => 'image',
        ]);
        $table->update();
    }
}
