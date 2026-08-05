<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Table;
use Cake\Validation\Validator;

class NewsTable extends Table
{
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('news');
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

            ->allowEmptyString('category_id');

        // 'image' is handled entirely in the controller: the raw uploaded
        // file never reaches the marshaller, same as Banners::background.

        return $validator;
    }
}