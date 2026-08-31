<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Table;
use Cake\Validation\Validator;

class NotificationsTable extends Table
{
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('notifications');
        $this->setPrimaryKey('id');
        $this->addBehavior('Timestamp');

        // Force 'settings' to be treated as JSON — see PagesTable for why
        // this can't be left to automatic detection on some hosts.
        $this->getSchema()->setColumnType('settings', 'json');
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

            ->date('valid_from')
            ->requirePresence('valid_from', 'create')
            ->notEmptyDate('valid_from')

            ->date('valid_to')
            ->requirePresence('valid_to', 'create')
            ->notEmptyDate('valid_to')
            ->add('valid_to', 'afterValidFrom', [
                'rule' => function ($value, array $context) {
                    $validFrom = $context['data']['valid_from'] ?? null;
                    if (!$validFrom || !$value) {
                        return true;
                    }

                    return $value >= $validFrom;
                },
                'message' => __('Must be on or after the start date.'),
            ]);

        // 'image' is handled entirely in the controller: the raw uploaded
        // file never reaches the marshaller, same as Banners::background.

        return $validator;
    }
}
