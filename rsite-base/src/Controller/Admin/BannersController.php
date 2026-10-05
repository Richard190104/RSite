<?php
declare(strict_types=1);

namespace App\Controller\Admin;

class BannersController extends \Rcore\Controller\Admin\BannersController
{
    use RcoreTemplateOverrideTrait;

    private const AI_CHAT_FIELDS = [
        'titleField' => 'title',
        'fields' => [
            ['target' => 'settings-subtitle', 'label' => 'short banner subtitle'],
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
