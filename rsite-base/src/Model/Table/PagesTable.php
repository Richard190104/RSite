<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Table;
use Cake\Validation\Validator;

class PagesTable extends Table
{
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('pages');
        $this->setPrimaryKey('id');
        $this->addBehavior('Timestamp');

        $this->belongsTo('NavbarCategories', [
            'foreignKey' => 'navbar_category_id',
        ]);

        // Force 'content' to be treated as JSON regardless of what the
        // database reports for the column — some MySQL/MariaDB versions
        // (seen on shared hosting) don't reflect a JSON column type
        // reliably, which makes the ORM try to bind a PHP array as a plain
        // string and fail.
        $this->getSchema()->setColumnType('content', 'json');
    }

    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->scalar('slug')
            ->maxLength('slug', 255)
            ->requirePresence('slug', 'create')
            ->notEmptyString('slug')
            ->add('slug', 'unique', ['rule' => 'validateUnique', 'provider' => 'table'])

            ->scalar('title')
            ->maxLength('title', 255)
            ->requirePresence('title', 'create')
            ->notEmptyString('title');

        return $validator;
    }
}
