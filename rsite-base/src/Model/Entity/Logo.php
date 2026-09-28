<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * @property int $id
 * @property string $name
 * @property string $path
 * @property array|null $options Fixed kind: {"type": "main"|"partner"}.
 * @property \Cake\I18n\DateTime $created
 * @property \Cake\I18n\DateTime $modified
 */
class Logo extends Entity
{
    protected array $_accessible = [
        'name' => true,
        'path' => true,
        'options' => true,
        'created' => true,
        'modified' => true,
    ];
}
