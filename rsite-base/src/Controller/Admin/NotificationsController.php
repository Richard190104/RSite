<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use Cake\Datasource\EntityInterface;

/**
 * Migrated onto the generic Resource pattern — the first trial of the
 * 'toggle' list column (the is_active quick-switch) and JSON-backed
 * checkbox fields (settings.is_active/show_as_popup). No app-side table
 * override needed: nothing outside this controller ever referenced the
 * bare 'Notifications' alias (confirmed when Notifications was first
 * extracted into the plugin), so this references the plugin's own table
 * directly via 'Rcore.Notifications'.
 */
class NotificationsController extends \Rcore\Controller\Admin\ResourceController
{
    protected function resourceConfig(?EntityInterface $item = null): array
    {
        return [
            'table' => 'Rcore.Notifications',
            'title' => __('Notifications'),
            'list' => [
                'columns' => [
                    [
                        'field' => 'settings.is_active', 'label' => __('Is active'), 'type' => 'toggle',
                    ],
                    ['field' => 'image', 'label' => __('Image'), 'type' => 'image', 'subdir' => 'notifications'],
                    ['field' => 'title', 'label' => __('Title')],
                    ['field' => 'valid_from', 'label' => __('Valid from')],
                    ['field' => 'valid_to', 'label' => __('Valid to')],
                ],
            ],
            'form' => [
                'fields' => [
                    ['name' => 'title', 'type' => 'text', 'label' => __('Title')],
                    ['name' => 'description', 'type' => 'textarea', 'label' => __('Description')],
                    ['name' => 'valid_from', 'type' => 'date', 'label' => __('Valid from')],
                    ['name' => 'valid_to', 'type' => 'date', 'label' => __('Valid to')],
                    ['name' => 'image', 'type' => 'image', 'label' => __('Image'), 'subdir' => 'notifications', 'required' => true],
                    ['name' => 'settings.is_active', 'type' => 'checkbox', 'label' => __('Is active')],
                    ['name' => 'settings.show_as_popup', 'type' => 'checkbox', 'label' => __('Show as popup')],
                ],
                'aiChatFields' => [
                    'titleField' => 'title',
                    'fields' => [
                        ['target' => 'description', 'label' => 'short site notification message'],
                    ],
                ],
            ],
        ];
    }
}
