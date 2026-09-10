<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<string, \App\Model\Entity\Configuration> $colors
 * @var bool $automaticImages
 *
 * One form for the whole palette (see Admin\ConfigurationsController::index()) —
 * grouped into sections purely for readability, all saved together. The
 * "Automatic images" toggle below is unrelated to the palette (it decides
 * whether a record with no image of its own gets a random stock photo or a
 * plain "no image" graphic — see PlaceholderImagesTable::random()) and
 * saves itself immediately on click via its own tiny form, same pattern as
 * Admin\BannersController::toggleEnabled().
 */
$this->assign('title', __('Configurations'));

// See Admin\AssistantController's 'palette' mode and
// webroot/js/admin-helper-widget.js's applyPalette() — a reply there fills
// every colors[slug] input below by name and submits #colors-form directly,
// with no separate "Use this" step.
$this->set('aiChatFields', ['paletteMode' => true]);

$groups = [
    __('Brand colors') => [
        'primary' => __('Primary'),
        'secondary' => __('Secondary'),
    ],
    __('Backgrounds & text') => [
        'bg' => __('Background'),
        'bg_alt' => __('Alternate background'),
        'text_muted' => __('Muted text'),
        'heading' => __('Heading text'),
        'link' => __('Link'),
        'link_hover' => __('Link (hover)'),
    ],
    __('Flash messages') => [
        'success_bg' => __('Success — background'),
        'success_text' => __('Success — text'),
        'success_border' => __('Success — border'),
        'warning_bg' => __('Warning — background'),
        'warning_text' => __('Warning — text'),
        'warning_border' => __('Warning — border'),
        'error_bg' => __('Error — background'),
        'error_text' => __('Error — text'),
        'error_border' => __('Error — border'),
        'info_bg' => __('Info — background'),
        'info_text' => __('Info — text'),
        'info_border' => __('Info — border'),
    ],
    __('Fishing grounds map') => [
        'reviry_map_bg' => __('Map background'),
    ],
];
?>
<div class="content form-card">
    <?= $this->Form->create(null, ['id' => 'colors-form']) ?>
        <?php foreach ($groups as $groupLabel => $fields): ?>
            <h3><?= $groupLabel ?></h3>
            <div class="form-grid">
                <?php foreach ($fields as $slug => $label): ?>
                    <?= $this->Form->control("colors.{$slug}", [
                        'type' => 'color',
                        'label' => $label,
                        'value' => $colors[$slug]->value ?? '#000000',
                    ]) ?>
                <?php endforeach; ?>
            </div>
        <?php endforeach; ?>
        <div class="form-card__actions">
            <?= $this->Form->button(__('Save')) ?>
        </div>
    <?= $this->Form->end() ?>
    <div class="form-card__actions">
        <?= $this->Form->postLink(
            __('Reset all to defaults'),
            ['action' => 'reset'],
            [
                'confirm' => __('Reset the whole palette to its original default colors? This cannot be undone.'),
                'class' => 'button button--secondary',
            ],
        ) ?>
    </div>

    <div class="form-card__hints">
        <p class="form-card__hint">
            <?= __(
                'These change the whole public site\'s look immediately — no rebuild or redeploy needed. The admin'
                    . ' panel itself keeps its own separate colors and is not affected.',
            ) ?>
        </p>
    </div>

    <div class="form-card__hints">
        <h3><?= __('Automatic images') ?></h3>
        <?= $this->Form->create(null, ['url' => ['action' => 'toggleAutomaticImages']]) ?>
            <div class="input checkbox">
                <label>
                    <?= $this->Form->checkbox('automatic_images', [
                        'checked' => $automaticImages,
                        'class' => 'js-toggle-checkbox',
                    ]) ?>
                    <?= __('Fill in a random photo for articles/events/fishing grounds with no image of their own') ?>
                </label>
            </div>
        <?= $this->Form->end() ?>
        <p class="form-card__hint">
            <?= __('When turned off, a record with no image of its own shows a plain "no image" graphic instead of a'
                . ' random stock photo.') ?>
        </p>
    </div>
</div>
