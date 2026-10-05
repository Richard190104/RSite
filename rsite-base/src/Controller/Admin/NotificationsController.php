<?php
declare(strict_types=1);

namespace App\Controller\Admin;

class NotificationsController extends \Rcore\Controller\Admin\NotificationsController
{
    use RcoreTemplateOverrideTrait;

    private const AI_CHAT_FIELDS = [
        'titleField' => 'title',
        'fields' => [
            ['target' => 'description', 'label' => 'short site notification message'],
        ],
    ];

    protected function aiChatFieldsByAction(): array
    {
        return [
            'add' => self::AI_CHAT_FIELDS,
            'edit' => self::AI_CHAT_FIELDS,
        ];
    }
}
