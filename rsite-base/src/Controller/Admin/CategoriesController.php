<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use Cake\Datasource\EntityInterface;

/**
 * Migrated onto the generic Resource pattern — trials the
 * 'reorder-hierarchy' list preset (a drag-reorderable top-level list, each
 * row's own children in their own drag-reorderable sub-list).
 */
class CategoriesController extends \Rcore\Controller\Admin\ResourceController
{
    protected function resourceConfig(?EntityInterface $item = null): array
    {
        $parentOptions = $this->fetchTable('Rcore.Categories')
            ->find()
            ->where($item !== null && $item->id ? ['id !=' => $item->id] : [])
            ->orderBy(['title' => 'ASC'])
            ->all()
            ->combine('id', 'title')
            ->toArray();

        return [
            'table' => 'Rcore.Categories',
            'title' => __('Categories'),
            'list' => [
                'preset' => 'reorder-hierarchy',
                'parentField' => 'parent_id',
                'childrenAssociation' => 'ChildCategories',
                'columns' => [
                    ['field' => 'title', 'label' => __('Title')],
                    ['field' => 'show_in_gallery', 'label' => __('Show in gallery'), 'type' => 'boolean'],
                ],
            ],
            'form' => [
                'fields' => [
                    ['name' => 'title', 'type' => 'text', 'label' => __('Title')],
                    [
                        'name' => 'parent_id', 'type' => 'select', 'label' => __('Parent category'),
                        'options' => $parentOptions, 'empty' => __('— top level —'),
                    ],
                    ['name' => 'show_in_gallery', 'type' => 'checkbox', 'label' => __('Show in gallery')],
                    ['name' => 'description', 'type' => 'textarea', 'label' => __('Description')],
                    ['name' => 'image', 'type' => 'image', 'label' => __('Image'), 'subdir' => 'categories'],
                ],
            ],
        ];
    }
}
