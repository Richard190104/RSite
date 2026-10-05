<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use Cake\Datasource\EntityInterface;
use Cake\Routing\Router;

class EventsController extends \Rcore\Controller\Admin\ResourceController
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
            $imageUrl = Router::url('/img/events/' . $item->image, true);
        }

        return [
            'table' => 'Events',
            'title' => __('Events'),
            'list' => [
                'contain' => ['Categories'],
                'order' => ['Events.date' => 'DESC'],
                'columns' => [
                    ['field' => 'image', 'label' => __('Image'), 'type' => 'image', 'subdir' => 'events'],
                    ['field' => 'title', 'label' => __('Title')],
                    ['field' => 'date', 'label' => __('Date')],
                    ['field' => 'location', 'label' => __('Location')],
                    ['field' => 'category.title', 'label' => __('Category')],
                ],
            ],
            'form' => [
                'fields' => [
                    ['name' => 'title', 'type' => 'text', 'label' => __('Title')],
                    ['name' => 'description', 'type' => 'textarea', 'label' => __('Description')],
                    ['name' => 'date', 'type' => 'date', 'label' => __('Date')],
                    ['name' => 'time', 'type' => 'text', 'label' => __('Time')],
                    ['name' => 'location', 'type' => 'text', 'label' => __('Location')],
                    [
                        'name' => 'category_id',
                        'type' => 'select',
                        'label' => __('Category'),
                        'options' => $categories,
                        'empty' => __('— none —'),
                    ],
                    ['name' => 'image', 'type' => 'image', 'label' => __('Image'), 'subdir' => 'events'],
                    ['name' => 'content', 'type' => 'wysiwyg', 'label' => __('Poster (HTML)')],
                ],
                'aiChatFields' => [
                    'titleField' => 'title',
                    'fields' => [
                        ['target' => 'description', 'label' => 'short event description'],
                        ['target' => 'content', 'label' => 'HTML poster for the event', 'kind' => 'html'],
                    ],
                    'imageUrl' => $imageUrl,
                ],
            ],
        ];
    }
}
