<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Table;
use Cake\ORM\TableRegistry;
use Cake\Validation\Validator;

class BannersTable extends Table
{
    /**
     * Locations that aren't real pages but a reserved placement for a group
     * of small banners (e.g. the homepage "about us" feature tiles).
     */
    public const VIRTUAL_LOCATIONS = [
        'home_mini' => 'Home — mini banner (about us tile)',
        'grounds-mini' => 'Home — fishing grounds tile',
        'grounds-mini-main' => 'Home — fishing grounds main image',
    ];

    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('banners');
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

            ->scalar('location')
            ->requirePresence('location', 'create')
            ->notEmptyString('location')
            ->add('location', 'validPage', [
                'rule' => function (string $value): bool {
                    if (array_key_exists($value, self::VIRTUAL_LOCATIONS)) {
                        return true;
                    }

                    return TableRegistry::getTableLocator()->get('Pages')->exists(['slug' => $value]);
                },
                'message' => __('Please select a valid location.'),
            ])

            ->boolean('is_enabled')
            ->allowEmptyString('is_enabled');

        // 'background' is handled entirely in the controller: the raw
        // uploaded file never reaches the marshaller (it can't be cast to
        // the string column), so it's not validated here either.

        return $validator;
    }
}