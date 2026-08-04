<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Table;
use Cake\ORM\TableRegistry;
use Cake\Validation\Validator;

class BannersTable extends Table
{
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('banners');
        $this->setPrimaryKey('id');
        $this->addBehavior('Timestamp');
    }

    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->scalar('title')
            ->maxLength('title', 255)
            ->requirePresence('title', 'create')
            ->notEmptyString('title')

            ->scalar('location')
            ->requirePresence('location', 'create')
            ->notEmptyString('location')
            ->add('location', 'validPage', [
                'rule' => function (string $value): bool {
                    return TableRegistry::getTableLocator()->get('Pages')->exists(['slug' => $value]);
                },
                'message' => __('Please select a valid page.'),
            ]);

        // 'background' is handled entirely in the controller: the raw
        // uploaded file never reaches the marshaller (it can't be cast to
        // the string column), so it's not validated here either.

        return $validator;
    }
}