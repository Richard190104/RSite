<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Table;

/**
 * Key/value site texts (title, name, organisation, city...). New rows are
 * added only via a migration — the admin UI edits existing values, it
 * never creates or removes rows.
 */
class TextsTable extends Table
{
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('texts');
        $this->setPrimaryKey('id');
        $this->addBehavior('Timestamp');
    }
}