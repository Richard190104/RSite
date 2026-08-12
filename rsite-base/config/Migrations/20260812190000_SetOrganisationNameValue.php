<?php
declare(strict_types=1);

use Migrations\BaseMigration;

/**
 * Fills in the value for the 'Organisation Name' text row added by
 * AddOrganisationNameToTexts — that migration only created the slot
 * (value NULL), this is the one-off seed for its actual content. Only
 * fills empty rows, so it won't clobber a value an admin already set.
 */
class SetOrganisationNameValue extends BaseMigration
{
    public function up(): void
    {
        $this->execute(
            "UPDATE texts SET value = 'MO SRZ Medzilaborce', modified = NOW()
             WHERE slug = 'Organisation Name' AND (value IS NULL OR value = '')"
        );
    }

    public function down(): void
    {
        $this->execute("UPDATE texts SET value = NULL WHERE slug = 'Organisation Name'");
    }
}