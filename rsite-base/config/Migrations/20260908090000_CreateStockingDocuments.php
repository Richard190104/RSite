<?php
declare(strict_types=1);

use Migrations\BaseMigration;

/**
 * "Zarybnenie" — nothing but a title and an attached PDF (e.g. a signed
 * stocking protocol). See FileUploadTrait, stored under
 * webroot/files/stocking-documents/.
 */
class CreateStockingDocuments extends BaseMigration
{
    public function change(): void
    {
        $table = $this->table('stocking_documents');
        $table->addColumn('title', 'string', [
            'default' => null,
            'limit' => 255,
            'null' => false,
        ]);
        $table->addColumn('file', 'string', [
            'default' => null,
            'limit' => 255,
            'null' => false,
            'comment' => 'PDF filename under webroot/files/stocking-documents/.',
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
