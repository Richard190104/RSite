<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Site-wide admin settings (see Admin\ConfigurationsController) — mostly
 * the customizable palette, a slug per CSS custom property declared on
 * :root in resources/scss/_base.scss, plus a handful of non-color flags
 * (see SETTING_SLUGS) that share this table rather than each needing their
 * own. New rows are added only via a migration — the admin UI edits
 * existing values, it never creates or removes rows.
 */
class ConfigurationsTable extends Table
{
    /**
     * Rows that hold something other than a hex color — excluded from
     * allAsSlugMap() (which becomes public --color-* CSS custom properties,
     * see templates/element/colorVariables.php) and exempted from the
     * hex-color validation rule below. The single source of truth for
     * "which rows in this table aren't part of the palette."
     */
    private const SETTING_SLUGS = ['automatic_images'];

    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('configurations');
        $this->setPrimaryKey('id');
        $this->addBehavior('Timestamp');
    }

    /**
     * A color row's value is only ever a plain 6-digit hex color: it's
     * spliced directly into a <style> block (see
     * templates/element/colorVariables.php), so anything looser than a
     * strict format check would let a saved value break out of that block.
     * Rows in SETTING_SLUGS aren't colors at all, so they're exempt.
     */
    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->scalar('value')
            ->requirePresence('value', 'create')
            ->notEmptyString('value')
            ->add('value', 'hexColor', [
                'rule' => ['custom', '/^#[0-9a-fA-F]{6}$/'],
                'message' => __('Enter a valid hex color, e.g. #143a6b.'),
                'on' => fn ($context) => !in_array($context['data']['slug'] ?? null, self::SETTING_SLUGS, true),
            ]);

        return $validator;
    }

    /**
     * Every color as slug => value (e.g. 'primary' => '#001a3b'), for
     * colorVariables.php to build the :root override from — SETTING_SLUGS
     * rows are excluded since they're not colors and have no matching CSS
     * custom property to emit.
     *
     * @return array<string, string>
     */
    public function allAsSlugMap(): array
    {
        $colors = [];
        foreach ($this->find()->select(['slug', 'value'])->where(['slug NOT IN' => self::SETTING_SLUGS])->all() as $color) {
            $colors[$color->slug] = $color->value;
        }

        return $colors;
    }

    /**
     * Whether a record with no image of its own (News/Event/FishingGround)
     * gets a random stock photo or a plain "no image" graphic — see
     * PlaceholderImagesTable::random(), the only reader of this flag.
     */
    public function automaticImagesEnabled(): bool
    {
        $value = $this->find()->select(['value'])->where(['slug' => 'automatic_images'])->first()?->value;

        return $value !== '0';
    }

    /**
     * The site's original palette (same values as this table's own seed
     * migration, config/Migrations/20260905131000_CreateColors.php) — the
     * single source of truth for Admin\ConfigurationsController::reset(),
     * which resets every color at once rather than one at a time.
     * Deliberately excludes SETTING_SLUGS rows (e.g. automatic_images) —
     * resetting the palette shouldn't also flip an unrelated setting back.
     *
     * @return array<string, string>
     */
    public function defaults(): array
    {
        return [
            'primary' => '#001a3b',
            'secondary' => '#e9e623',
            'bg' => '#ffffff',
            'bg_alt' => '#f5f7fa',
            'text_muted' => '#454e56',
            'heading' => '#363637',
            'link' => '#2f85ae',
            'link_hover' => '#2a6496',
            'success_bg' => '#e3fcec',
            'success_text' => '#1f9d55',
            'success_border' => '#51d88a',
            'warning_bg' => '#fffabc',
            'warning_text' => '#8d7b00',
            'warning_border' => '#d3b800',
            'error_bg' => '#fcebea',
            'error_text' => '#cc1f1a',
            'error_border' => '#ef5753',
            'info_bg' => '#eff8ff',
            'info_text' => '#2779bd',
            'info_border' => '#6cb2eb',
            'reviry_map_bg' => '#f4ecd8',
        ];
    }
}
