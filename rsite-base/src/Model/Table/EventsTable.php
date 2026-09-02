<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Table;
use Cake\Validation\Validator;

class EventsTable extends Table
{
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('events');
        $this->setPrimaryKey('id');
        $this->addBehavior('Timestamp');

        $this->belongsTo('Categories', [
            'foreignKey' => 'category_id',
        ]);
    }

    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->scalar('title')
            ->maxLength('title', 255)
            ->requirePresence('title', 'create')
            ->notEmptyString('title')

            ->scalar('description')
            ->requirePresence('description', 'create')
            ->notEmptyString('description')

            ->date('date')
            ->requirePresence('date', 'create')
            ->notEmptyDate('date')

            ->scalar('location')
            ->maxLength('location', 255)
            ->allowEmptyString('location')

            ->scalar('time')
            ->maxLength('time', 32)
            ->allowEmptyString('time')

            ->allowEmptyString('category_id');

        return $validator;
    }
}