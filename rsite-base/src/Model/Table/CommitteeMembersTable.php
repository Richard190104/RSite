<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * The organisation's committee (výbor) — name, and optionally phone, email,
 * a photo. Public-facing display isn't built yet; this is admin-managed
 * data only for now.
 */
class CommitteeMembersTable extends Table
{
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('committee_members');
        $this->setPrimaryKey('id');
        $this->addBehavior('Timestamp');
    }

    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->scalar('name')
            ->maxLength('name', 255)
            ->requirePresence('name', 'create')
            ->notEmptyString('name')

            ->scalar('role')
            ->maxLength('role', 255)
            ->allowEmptyString('role')

            ->scalar('section')
            ->maxLength('section', 255)
            ->requirePresence('section', 'create')
            ->notEmptyString('section')

            ->scalar('phone')
            ->maxLength('phone', 40)
            ->allowEmptyString('phone')

            ->email('email')
            ->allowEmptyString('email');

        // 'photo' is handled entirely in the controller: the raw uploaded
        // file never reaches the marshaller, same as Banners::background.

        return $validator;
    }
}
