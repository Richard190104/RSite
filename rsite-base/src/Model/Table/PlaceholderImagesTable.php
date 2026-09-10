<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Table;
use Cake\ORM\TableRegistry;

/**
 * A fixed pool of stock nature photos (webroot/img/placeholders/), each row
 * just a filename — shown via random() instead of a plain CSS gradient
 * whenever a News article, Event, or FishingGround has no image of its own.
 * New rows are added only via a migration — see CreatePlaceholderImages.
 */
class PlaceholderImagesTable extends Table
{
    private const NO_IMAGE_FILENAME = 'no-image.svg';

    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('placeholder_images');
        $this->setPrimaryKey('id');
        $this->addBehavior('Timestamp');
    }

    /**
     * A filename from the pool (e.g. 'placeholder-03.jpg') for a template to
     * build a URL under /img/placeholders/ from — random unless "Automatic
     * Images" is off, in which case it's always the same neutral "no image"
     * graphic instead of a random stock photo. Never null: even the "off"
     * case still returns a real filename, so nothing calling this needs to
     * handle an empty path.
     */
    public function random(): string
    {
        if (!$this->automaticImagesEnabled()) {
            return self::NO_IMAGE_FILENAME;
        }

        return $this->find()
            ->select(['filename'])
            ->orderBy(['rand()'])
            ->first()?->filename ?? self::NO_IMAGE_FILENAME;
    }

    private function automaticImagesEnabled(): bool
    {
        return TableRegistry::getTableLocator()->get('Configurations')->automaticImagesEnabled();
    }
}
