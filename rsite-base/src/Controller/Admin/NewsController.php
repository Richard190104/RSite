<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use Cake\Datasource\EntityInterface;
use Cake\Routing\Router;

/**
 * First app-owned section migrated onto the generic Resource pattern (see
 * Rcore\Controller\Admin\ResourceController) — NewsTable/News entity are
 * untouched, only the admin controller/templates are replaced.
 */
class NewsController extends \Rcore\Controller\Admin\ResourceController
{
    protected function resourceConfig(?EntityInterface $item = null): array
    {
        $categories = $this->fetchTable('Categories')
            ->find()
            ->orderBy(['title' => 'ASC'])
            ->all()
            ->combine('id', 'title')
            ->toArray();

        $imageUrl = '';
        if ($item !== null && $item->image) {
            $imageUrl = Router::url('/img/news/' . $item->image, true);
        }

        return [
            'table' => 'News',
            'title' => __('News'),
            'list' => [
                'contain' => ['Categories'],
                'columns' => [
                    ['field' => 'image', 'label' => __('Image'), 'type' => 'image', 'subdir' => 'news'],
                    ['field' => 'title', 'label' => __('Title')],
                    ['field' => 'category.title', 'label' => __('Category')],
                    ['field' => 'date', 'label' => __('Date')],
                ],
            ],
            'form' => [
                'fields' => [
                    ['name' => 'title', 'type' => 'text', 'label' => __('Title')],
                    ['name' => 'description', 'type' => 'textarea', 'label' => __('Description')],
                    ['name' => 'content', 'type' => 'wysiwyg', 'label' => __('Poster (HTML)')],
                    ['name' => 'date', 'type' => 'date', 'label' => __('Date')],
                    [
                        'name' => 'category_id',
                        'type' => 'select',
                        'label' => __('Category'),
                        'options' => $categories,
                        'empty' => __('— none —'),
                    ],
                    ['name' => 'image', 'type' => 'image', 'label' => __('Image'), 'subdir' => 'news'],
                ],
                'aiChatFields' => [
                    'titleField' => 'title',
                    'fields' => [
                        ['target' => 'description', 'label' => 'short news article summary'],
                        ['target' => 'content', 'label' => 'HTML poster for the news article', 'kind' => 'html'],
                    ],
                    'imageUrl' => $imageUrl,
                ],
            ],
        ];
    }
}
