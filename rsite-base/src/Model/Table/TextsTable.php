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

    /**
     * Value of a single text, for templates that need one string (navbar
     * brand, footer contact...). Returns $default when the row is missing or
     * its value was never filled in, so a half-configured site still renders.
     */
    public function value(string $slug, string $default = ''): string
    {
        $value = $this->find()
            ->select(['value'])
            ->where(['slug' => $slug])
            ->first()?->value;

        return $value === null || $value === '' ? $default : $value;
    }
}