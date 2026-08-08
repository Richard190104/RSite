<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Table;

/**
 * Site logos (header, footer...). Like TextsTable, the rows are the fixed
 * set of logo slots the templates ask for — new rows are added only via a
 * migration, the admin UI just replaces the image behind an existing slot.
 */
class LogosTable extends Table
{
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('logos');
        $this->setPrimaryKey('id');
        $this->addBehavior('Timestamp');
    }

    // 'path' is handled entirely in the controller: the raw uploaded file
    // never reaches the marshaller (it can't be cast to the string column),
    // so it's not validated here either — see ImageUploadTrait.
}
