<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * @property int $id
 * @property string $title
 * @property \Cake\I18n\DateTime $created
 * @property \Cake\I18n\DateTime $modified
 * @property \App\Model\Entity\Page[] $pages
 */
class NavbarCategory extends Entity
{
    protected array $_accessible = [
        'title' => true,
        'created' => true,
        'modified' => true,
        'pages' => true,
    ];
}