<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use Cake\Core\Configure;
use Cake\Datasource\EntityInterface;

/**
 * Migrated onto the generic Resource pattern — second trial of the
 * 'toggle' list column (is_enabled) and a 'select' field whose options
 * are computed from two sources (real Pages + Configure-registered virtual
 * locations), same as the original hand-written pageOptions().
 */
class BannersController extends \Rcore\Controller\Admin\ResourceController
{
    protected function resourceConfig(?EntityInterface $item = null): array
    {
        // Bare 'Pages', not 'Rcore.Pages' — Pages is a deliberately
        // overridable core table, see BannersTable's own comment.
        $locations = $this->fetchTable('Pages')
            ->find()
            ->orderBy(['title' => 'ASC'])
            ->all()
            ->combine('slug', 'title')
            ->toArray()
            + (array)Configure::read('Rcore.bannerVirtualLocations', []);

        return [
            'table' => 'Rcore.Banners',
            'title' => __('Banners'),
            'list' => [
                'order' => ['location' => 'ASC'],
                'columns' => [
                    ['field' => 'is_enabled', 'label' => __('Show on the page'), 'type' => 'toggle'],
                    ['field' => 'title', 'label' => __('Title')],
                    ['field' => 'location', 'label' => __('Location'), 'type' => 'select', 'options' => $locations],
                    ['field' => 'background', 'label' => __('Background'), 'type' => 'image', 'subdir' => 'banners'],
                ],
            ],
            'form' => [
                'fields' => [
                    ['name' => 'title', 'type' => 'text', 'label' => __('Title')],
                    ['name' => 'settings.subtitle', 'type' => 'text', 'label' => __('Subtitle')],
                    ['name' => 'location', 'type' => 'select', 'label' => __('Location'), 'options' => $locations],
                    [
                        'name' => 'background', 'type' => 'image', 'label' => __('Background'),
                        'subdir' => 'banners', 'required' => true,
                    ],
                    ['name' => 'is_enabled', 'type' => 'checkbox', 'label' => __('Show on the page')],
                ],
                'aiChatFields' => [
                    'titleField' => 'title',
                    'fields' => [
                        ['target' => 'settings-subtitle', 'label' => 'short banner subtitle'],
                    ],
                ],
            ],
        ];
    }
}
