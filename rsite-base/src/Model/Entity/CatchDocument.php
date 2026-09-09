<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * @property int $id
 * @property string $title
 * @property string $file PDF filename under webroot/files/catch-documents/.
 * @property \Cake\I18n\DateTime $created
 * @property \Cake\I18n\DateTime $modified
 */
class CatchDocument extends Entity
{
    protected array $_accessible = [
        'title' => true,
        'file' => true,
        'created' => true,
        'modified' => true,
    ];
}
