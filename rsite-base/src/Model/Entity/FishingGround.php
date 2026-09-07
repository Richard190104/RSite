<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * @property int $id
 * @property string $title
 * @property string|null $registration_number
 * @property string|null $type
 * @property string|null $description
 * @property string|null $image
 * @property string|null $location
 * @property string|null $position_x Percentage (0-100) position on the stylized map's X axis — see templates/element/reviryMap.php.
 * @property string|null $position_y Percentage (0-100) position on the stylized map's Y axis — see templates/element/reviryMap.php.
 * @property \Cake\I18n\DateTime $created
 * @property \Cake\I18n\DateTime $modified
 */
class FishingGround extends Entity
{
    protected array $_accessible = [
        'title' => true,
        'registration_number' => true,
        'type' => true,
        'description' => true,
        'image' => true,
        'location' => true,
        'position_x' => true,
        'position_y' => true,
        'created' => true,
        'modified' => true,
    ];
}
