<?php
declare(strict_types=1);

namespace App\Model\Entity;

/**
 * @property int|null $navbar_category_id
 * @property int $position
 * @property \App\Model\Entity\NavbarCategory|null $navbar_category
 */
class Page extends \Rcore\Model\Entity\Page
{
    // Restates the plugin's own fields alongside this app's extra ones — a
    // child class's $_accessible replaces the parent's, it doesn't merge
    // (see this plugin's README).
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
