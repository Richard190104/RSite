<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * @property int $id
 * @property string $name
 * @property string|null $role
 * @property string $section
 * @property string|null $phone
 * @property string|null $email
 * @property string|null $photo
 * @property \Cake\I18n\DateTime $created
 * @property \Cake\I18n\DateTime $modified
 */
class CommitteeMember extends Entity
{
    protected array $_accessible = [
        'name' => true,
        'role' => true,
        'section' => true,
        'phone' => true,
        'email' => true,
        'photo' => true,
        'created' => true,
        'modified' => true,
    ];
}
