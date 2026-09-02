<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class AddDeleteUrlToGalleries extends BaseMigration
{
    /**
     * Change Method.
     *
     * More information on this method is available here:
     * https://book.cakephp.org/migrations/5/guides/writing-migrations/migration-methods.html#the-change-method
     *
     * @return void
     */
    public function change(): void
    {
        $table = $this->table('galleries');
        // Photos are hosted on ImgBB instead of webroot/img/ (a public
        // gallery can accumulate far more images than this host's disk
        // quota comfortably holds) — 'image' now stores the ImgBB-hosted
        // URL instead of a local filename, and this column stores the
        // matching delete_url ImgBB returns at upload time, needed to
        // remove the image from ImgBB later (see Admin\ImgBbUploadTrait).
        $table->addColumn('delete_url', 'string', [
            'default' => null,
            'limit' => 255,
            'null' => true,
        ]);
        $table->update();
    }
}
