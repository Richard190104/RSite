<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use Cake\Datasource\EntityInterface;

class CommitteeMembersController extends \Rcore\Controller\Admin\ResourceController
{
    protected function resourceConfig(?EntityInterface $item = null): array
    {
        return [
            'table' => 'CommitteeMembers',
            'title' => __('Committee members'),
            'list' => [
                'order' => ['section' => 'ASC', 'name' => 'ASC'],
                'columns' => [
                    ['field' => 'photo', 'label' => __('Photo'), 'type' => 'image', 'subdir' => 'committee'],
                    ['field' => 'name', 'label' => __('Name')],
                    ['field' => 'role', 'label' => __('Role')],
                    ['field' => 'section', 'label' => __('Section')],
                    ['field' => 'phone', 'label' => __('Phone')],
                    ['field' => 'email', 'label' => __('Email')],
                ],
            ],
            'form' => [
                'fields' => [
                    ['name' => 'name', 'type' => 'text', 'label' => __('Name')],
                    ['name' => 'role', 'type' => 'text', 'label' => __('Role')],
                    ['name' => 'section', 'type' => 'text', 'label' => __('Section')],
                    ['name' => 'phone', 'type' => 'text', 'label' => __('Phone')],
                    ['name' => 'email', 'type' => 'text', 'label' => __('Email')],
                    ['name' => 'photo', 'type' => 'image', 'label' => __('Photo'), 'subdir' => 'committee'],
                ],
            ],
        ];
    }
}
