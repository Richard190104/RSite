<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * @property int $id
 * @property string $title
 * @property string $background
 * @property string $location
 * @property bool $is_enabled
 * @property array|null $settings
 * @property \Cake\I18n\DateTime $created
 * @property \Cake\I18n\DateTime $modified
 */
class Banner extends Entity
{
    protected array $_accessible = [
        'title' => true,
        'background' => true,
        'location' => true,
        'is_enabled' => true,
        'settings' => true,
        'created' => true,
        'modified' => true,
    ];
}