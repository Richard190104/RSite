<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use Cake\Datasource\EntityInterface;

/**
 * Migrated onto the generic Resource pattern (see
 * Rcore\Controller\Admin\ResourceController) as the second trial after
 * AboutUsItems — this is the first resource needing real behavior from the
 * generic controller (an image upload, a JSON-backed select field), not
 * just plain text/wysiwyg. LogosTable itself (byType()/mainPath()/
 * partners(), used by the public site) is completely untouched — only the
 * admin controller/templates are replaced.
 */
class LogosController extends \Rcore\Controller\Admin\ResourceController
{
    protected function resourceConfig(?EntityInterface $item = null): array
    {
        $kindOptions = [
            \Rcore\Model\Table\LogosTable::TYPE_MAIN => __('Main'),
            \Rcore\Model\Table\LogosTable::TYPE_PARTNER => __('Partner'),
        ];

        return [
            'table' => 'Logos',
            'title' => __('Logos'),
            'list' => [
                'columns' => [
                    ['field' => 'name', 'label' => __('Logo')],
                    ['field' => 'options.type', 'label' => __('Kind'), 'type' => 'select', 'options' => $kindOptions],
                    ['field' => 'path', 'label' => __('Image'), 'type' => 'image', 'subdir' => 'logos'],
                ],
            ],
            'form' => [
                'fields' => [
                    ['name' => 'name', 'type' => 'text', 'label' => __('Name')],
                    ['name' => 'options.type', 'type' => 'select', 'label' => __('Kind'), 'options' => $kindOptions],
                    ['name' => 'path', 'type' => 'image', 'label' => __('Image'), 'subdir' => 'logos', 'required' => true],
                ],
            ],
        ];
    }
}
