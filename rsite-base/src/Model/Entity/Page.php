<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * @property int $id
 * @property string $slug
 * @property string $title
 * @property array|null $content
 * @property \Cake\I18n\DateTime $created
 * @property \Cake\I18n\DateTime $modified
 */
class Page extends Entity
{
    protected array $_accessible = [
        'slug' => true,
        'title' => true,
        'content' => true,
        'created' => true,
        'modified' => true,
    ];
}