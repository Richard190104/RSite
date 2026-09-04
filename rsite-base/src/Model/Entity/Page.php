<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * @property int $id
 * @property string $slug
 * @property string $title
 * @property array|null $content
 * @property int|null $navbar_category_id
 * @property int $position
 * @property \Cake\I18n\DateTime $created
 * @property \Cake\I18n\DateTime $modified
 * @property \App\Model\Entity\NavbarCategory|null $navbar_category
 */
class Page extends Entity
{
    protected array $_accessible = [
        'slug' => true,
        'title' => true,
        'content' => true,
        'navbar_category_id' => true,
        'position' => true,
        'created' => true,
        'modified' => true,
    ];
}