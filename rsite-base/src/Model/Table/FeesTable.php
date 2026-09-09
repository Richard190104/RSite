<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Table;
use Cake\Validation\Validator;

class FeesTable extends Table
{
    /**
     * Fixed set of categories the public "poplatky" page groups fees into
     * — unlike News/Events/Galleries, these aren't rows in the shared
     * Categories table, since the price-list sections are a fixed set
     * specific to this one page, not admin-manageable categories.
     */
    public const CATEGORIES = [
        'membership_stamps' => 'Membership stamps',
        'registration_fee' => 'Registration fee',
        'fishing_permits' => 'Fishing permits',
        'forms' => 'Forms',
        'existing_members_total' => 'Total fees for existing members',
        'new_members_total' => 'Total fees for new members',
        'other_fees' => 'Other fees',
    ];

    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('fees');
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

            ->scalar('price')
            ->maxLength('price', 255)
            ->requirePresence('price', 'create')
            ->notEmptyString('price')

            ->scalar('category')
            ->requirePresence('category', 'create')
            ->notEmptyString('category')
            ->inList('category', array_keys(self::CATEGORIES))

            ->integer('position')
            ->allowEmptyString('position');

        return $validator;
    }
}
