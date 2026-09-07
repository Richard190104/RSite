<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * The site's customizable palette (see Admin\ColorsController) — a slug per
 * CSS custom property declared on :root in resources/scss/_base.scss. New
 * rows are added only via a migration — the admin UI edits existing values,
 * it never creates or removes rows.
 */
class ColorsTable extends Table
{
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('colors');
        $this->setPrimaryKey('id');
        $this->addBehavior('Timestamp');
    }

    /**
     * Only ever a plain 6-digit hex color: this value is spliced directly
     * into a <style> block (see templates/element/colorVariables.php), so
     * anything looser than a strict format check would let a saved value
     * break out of that block.
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
            ]);

        return $validator;
    }

    /**
     * All colors as slug => value (e.g. 'primary' => '#001a3b'), for
     * colorVariables.php to build the :root override from.
     *
     * @return array<string, string>
     */
    public function allAsSlugMap(): array
    {
        $colors = [];
        foreach ($this->find()->select(['slug', 'value'])->all() as $color) {
            $colors[$color->slug] = $color->value;
        }

        return $colors;
    }

    /**
     * The site's original palette (same values as this table's own seed
     * migration, config/Migrations/20260905131000_CreateColors.php) — the
     * single source of truth for Admin\ColorsController::reset(), which
     * resets every row at once rather than one at a time.
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
