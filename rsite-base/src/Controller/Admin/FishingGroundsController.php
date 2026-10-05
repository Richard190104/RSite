<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use Cake\Datasource\EntityInterface;

class FishingGroundsController extends \Rcore\Controller\Admin\ResourceController
{
    protected function resourceConfig(?EntityInterface $item = null): array
    {
        return [
            'table' => 'FishingGrounds',
            'title' => __('Fishing grounds'),
            'list' => [
                'order' => ['title' => 'ASC'],
                'columns' => [
                    ['field' => 'image', 'label' => __('Image'), 'type' => 'image', 'subdir' => 'fishing-grounds'],
                    ['field' => 'title', 'label' => __('Title')],
                    ['field' => 'registration_number', 'label' => __('Registration number')],
                    ['field' => 'type', 'label' => __('Type')],
                    ['field' => 'location', 'label' => __('Location')],
                ],
            ],
            'form' => [
                'fields' => [
                    ['name' => 'title', 'type' => 'text', 'label' => __('Title')],
                    ['name' => 'registration_number', 'type' => 'text', 'label' => __('Registration number')],
                    ['name' => 'type', 'type' => 'text', 'label' => __('Type')],
                    ['name' => 'description', 'type' => 'textarea', 'label' => __('Description')],
                    ['name' => 'location', 'type' => 'text', 'label' => __('Location')],
                    ['name' => 'position_x', 'type' => 'text', 'label' => __('Position X (0-100)')],
                    ['name' => 'position_y', 'type' => 'text', 'label' => __('Position Y (0-100)')],
                    ['name' => 'image', 'type' => 'image', 'label' => __('Image'), 'subdir' => 'fishing-grounds'],
                ],
            ],
        ];
    }
}
