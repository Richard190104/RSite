<?php
declare(strict_types=1);

use Migrations\BaseMigration;

/**
 * "Úlovky" — nothing but a title and an attached PDF. Same shape as
 * stocking_documents, kept as its own table (not a shared "documents" table
 * with a type column) so the two lists stay simple to query/manage
 * separately. Stored under webroot/files/catch-documents/.
 */
class CreateCatchDocuments extends BaseMigration
{
    public function change(): void
    {
        $table = $this->table('catch_documents');
        $table->addColumn('title', 'string', [
            'default' => null,
            'limit' => 255,
            'null' => false,
        ]);
        $table->addColumn('file', 'string', [
            'default' => null,
            'limit' => 255,
            'null' => false,
            'comment' => 'PDF filename under webroot/files/catch-documents/.',
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
    }
}
