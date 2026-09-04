<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * @property int $id
 * @property string $title
 * @property int $position
 * @property int|null $parent_id
 * @property bool $show_in_gallery
 * @property string|null $image
 * @property string|null $description
 * @property \Cake\I18n\DateTime $created
 * @property \Cake\I18n\DateTime $modified
 * @property \App\Model\Entity\Category|null $parent_category
 * @property array<\App\Model\Entity\Category>|null $child_categories
 * @property string|null $thumbnail_url Set by GalleryController — the
 *   admin-uploaded `image` when present, otherwise the first photo of this
 *   category or (for a top-level category) one of its subcategories.
 */
class Category extends Entity
{
    protected array $_accessible = [
        'title' => true,
        'position' => true,
        'parent_id' => true,
        'show_in_gallery' => true,
        'image' => true,
        'description' => true,
        'created' => true,
        'modified' => true,
    ];
}