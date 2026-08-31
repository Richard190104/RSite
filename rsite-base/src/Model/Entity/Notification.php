<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * @property int $id
 * @property string $title
 * @property string $description
 * @property string $image
 * @property \Cake\I18n\Date $valid_from
 * @property \Cake\I18n\Date $valid_to
 * @property array|null $settings
 * @property \Cake\I18n\DateTime $created
 * @property \Cake\I18n\DateTime $modified
 */
class Notification extends Entity
{
    protected array $_accessible = [
        'title' => true,
        'description' => true,
        'image' => true,
        'valid_from' => true,
        'valid_to' => true,
        'settings' => true,
        'created' => true,
        'modified' => true,
    ];
}
