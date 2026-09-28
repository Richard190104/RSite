<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class AddOptionsToLogos extends BaseMigration
{
    /**
     * Fixed kind on each logo — "main" (header/footer, only the first one
     * is shown) or "partner" (homepage strip). Not a categories-table
     * relation. The empty partner slots from AddPartnerLogos were
     * placeholders; logos are added from the admin now.
     *
     * @return void
     */
    public function change(): void
    {
        $table = $this->table('logos');
        $table->addColumn('options', 'json', [
            'default' => null,
            'null' => true,
        ]);
        $table->update();

        $this->execute("UPDATE logos SET options = '{\"type\":\"main\"}' WHERE name = 'Main logo'");
        $this->execute(
            "DELETE FROM logos WHERE name IN ("
            . "'Partner — local organisation', 'Partner — Slovak Fishing Association', 'Partner — city'"
            . ')',
        );
    }
}
