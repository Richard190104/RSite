<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Table;
use Cake\Validation\Validator;

class FishingGroundsTable extends Table
{
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('fishing_grounds');
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

            ->scalar('registration_number')
            ->maxLength('registration_number', 255)
            ->allowEmptyString('registration_number')

            ->scalar('type')
            ->maxLength('type', 255)
            ->allowEmptyString('type')

            ->scalar('description')
            ->allowEmptyString('description')

            ->scalar('location')
            ->maxLength('location', 255)
            ->allowEmptyString('location')

            ->decimal('position_x')
            ->range('position_x', [0, 100], __('Position must be between 0 and 100.'))
            ->allowEmptyString('position_x')

            ->decimal('position_y')
            ->range('position_y', [0, 100], __('Position must be between 0 and 100.'))
            ->allowEmptyString('position_y');

        // 'image' is handled entirely in the controller: the raw uploaded
        // file never reaches the marshaller, same as Events::image.

        return $validator;
    }
}
