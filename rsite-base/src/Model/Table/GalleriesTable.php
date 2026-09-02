<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Table;
use Cake\Validation\Validator;

class GalleriesTable extends Table
{
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('galleries');
        $this->setPrimaryKey('id');
        $this->addBehavior('Timestamp');

        $this->belongsTo('Categories', [
            'foreignKey' => 'category_id',
        ]);
    }

    public function validationDefault(Validator $validator): Validator
    {
        $validator->allowEmptyString('category_id');

        // 'image' and 'delete_url' are handled entirely in the controller
        // (see Admin\ImgBbUploadTrait) — 'image' holds the ImgBB-hosted
        // URL, 'delete_url' the matching link needed to remove it later.
        $validator->allowEmptyString('delete_url');

        // Optional per-photo caption, shown instead of the parent category
        // name on the public gallery (see templates/Gallery/cards.php) —
        // capped short so it always fits the caption bar on one line.
        $validator
            ->scalar('text')
            ->maxLength('text', 80)
            ->allowEmptyString('text');

        return $validator;
    }
}