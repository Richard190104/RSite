<?php
declare(strict_types=1);

use Migrations\BaseMigration;

/**
 * The site's customizable palette — a dedicated key/value table (own admin
 * section, see Admin\ColorsController), separate from `texts`: colors need
 * a native color-picker UI and are always grouped/labeled, which doesn't
 * fit the generic Texts screen. Each slug corresponds 1:1 to a CSS custom
 * property declared on :root in resources/scss/_base.scss (slug
 * `primary` -> `--color-primary`) — see templates/element/colorVariables.php,
 * which reads every row here to build the <style> override the public
 * layout injects after the compiled stylesheet. Rows are pre-filled with
 * the site's current default hex values (never null) since a color always
 * needs a concrete value to render valid CSS. New rows are added only via
 * a migration, same convention as `texts` — the admin UI edits existing
 * values, it never creates or removes rows.
 */
class CreateColors extends BaseMigration
{
    public function change(): void
    {
        $table = $this->table('colors');
        $table->addColumn('slug', 'string', [
            'default' => null,
            'limit' => 255,
            'null' => false,
        ]);
        $table->addColumn('value', 'string', [
            'default' => null,
            'limit' => 20,
            'null' => false,
        ]);
        $table->addColumn('created', 'datetime', [
            'default' => null,
            'null' => false,
        ]);
        $table->addColumn('modified', 'datetime', [
            'default' => null,
            'null' => false,
        ]);
        $table->addIndex(['slug'], [
            'name' => 'UNIQUE_SLUG',
            'unique' => true,
        ]);
        $table->create();

        $now = date('Y-m-d H:i:s');
        $table->insert([
            ['slug' => 'primary', 'value' => '#001a3b', 'created' => $now, 'modified' => $now],
            ['slug' => 'secondary', 'value' => '#ec2828', 'created' => $now, 'modified' => $now],
            ['slug' => 'bg', 'value' => '#ffffff', 'created' => $now, 'modified' => $now],
            ['slug' => 'bg_alt', 'value' => '#f5f7fa', 'created' => $now, 'modified' => $now],
            ['slug' => 'text_muted', 'value' => '#454e56', 'created' => $now, 'modified' => $now],
            ['slug' => 'heading', 'value' => '#363637', 'created' => $now, 'modified' => $now],
            ['slug' => 'link', 'value' => '#2f85ae', 'created' => $now, 'modified' => $now],
            ['slug' => 'link_hover', 'value' => '#2a6496', 'created' => $now, 'modified' => $now],
            ['slug' => 'success_bg', 'value' => '#e3fcec', 'created' => $now, 'modified' => $now],
            ['slug' => 'success_text', 'value' => '#1f9d55', 'created' => $now, 'modified' => $now],
            ['slug' => 'success_border', 'value' => '#51d88a', 'created' => $now, 'modified' => $now],
            ['slug' => 'warning_bg', 'value' => '#fffabc', 'created' => $now, 'modified' => $now],
            ['slug' => 'warning_text', 'value' => '#8d7b00', 'created' => $now, 'modified' => $now],
            ['slug' => 'warning_border', 'value' => '#d3b800', 'created' => $now, 'modified' => $now],
            ['slug' => 'error_bg', 'value' => '#fcebea', 'created' => $now, 'modified' => $now],
            ['slug' => 'error_text', 'value' => '#cc1f1a', 'created' => $now, 'modified' => $now],
            ['slug' => 'error_border', 'value' => '#ef5753', 'created' => $now, 'modified' => $now],
            ['slug' => 'info_bg', 'value' => '#eff8ff', 'created' => $now, 'modified' => $now],
            ['slug' => 'info_text', 'value' => '#2779bd', 'created' => $now, 'modified' => $now],
            ['slug' => 'info_border', 'value' => '#6cb2eb', 'created' => $now, 'modified' => $now],
        ])->saveData();
    }
}
