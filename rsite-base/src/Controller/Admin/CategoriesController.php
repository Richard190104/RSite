<?php
declare(strict_types=1);

namespace App\Controller\Admin;

class CategoriesController extends \Rcore\Controller\Admin\CategoriesController
{
    use RcoreTemplateOverrideTrait;

    private const AI_CHAT_FIELDS = [
        'titleField' => 'title',
        'fields' => [
            ['target' => 'description', 'label' => 'short category description'],
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
