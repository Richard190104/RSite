<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class ReplaceLatLngWithPositionOnFishingGrounds extends BaseMigration
{
    public function change(): void
    {
        $table = $this->table('fishing_grounds');
        $table->removeColumn('latitude');
        $table->removeColumn('longitude');
        // Percentage position (0-100) on the stylized map's X/Y axes — set
        // by an admin dragging/eyeballing a pin on the map illustration
        // (templates/element/reviryMap.php), not real GPS coordinates.
        // precision 5, scale 2 allows e.g. 100.00 with 2 decimal places.
        $table->addColumn('position_x', 'decimal', [
            'default' => null,
            'precision' => 5,
            'scale' => 2,
            'null' => true,
            'after' => 'location',
        ]);
        $table->addColumn('position_y', 'decimal', [
            'default' => null,
            'precision' => 5,
            'scale' => 2,
            'null' => true,
            'after' => 'position_x',
        ]);
        $table->update();
    }
}
