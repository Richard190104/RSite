<?php
declare(strict_types=1);

namespace App\Controller\Admin;

class NavbarCategoriesController extends \Rcore\Controller\Admin\NavbarCategoriesController
{
    use RcoreTemplateOverrideTrait;

    protected function aiChatFieldsByAction(): array
    {
        return [
            'add' => ['titleField' => 'title'],
            'edit' => ['titleField' => 'title'],
        ];
    }
}
