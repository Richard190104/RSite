<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Table;

/**
 * A fixed pool of stock nature photos (webroot/img/placeholders/), each row
 * just a filename — shown via random() instead of a plain CSS gradient
 * whenever a News article, Event, or FishingGround has no image of its own.
 * New rows are added only via a migration — see CreatePlaceholderImages.
 */
class PlaceholderImagesTable extends Table
{
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('placeholder_images');
        $this->setPrimaryKey('id');
        $this->addBehavior('Timestamp');
    }

    /**
     * One random filename from the pool (e.g. 'placeholder-03.jpg'), for a
     * template to build a URL under /img/placeholders/ from. Null only if
     * the table is empty, which never happens outside of a broken seed.
     */
    public function random(): ?string
    {
        return $this->find()
            ->select(['filename'])
            ->orderBy(['rand()'])
            ->first()?->filename;
    }
}
