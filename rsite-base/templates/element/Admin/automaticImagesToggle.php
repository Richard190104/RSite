<?php
/**
 * @var \App\View\AppView $this
 *
 * Rendered at the bottom of the shared Rcore Configurations screen via
 * Configure::write('Rcore.configurationsExtraElements', [...]) in
 * Application.php — unrelated to the color palette, just convenient to
 * show on the same page. Posts to Admin\PlaceholderImagesController
 * (app-side; the shared Rcore\Controller\Admin\ConfigurationsController
 * only ever deals with colors).
 */
use Cake\ORM\TableRegistry;

$automaticImages = TableRegistry::getTableLocator()->get('Rcore.Configurations')
    ->find()
    ->select(['value'])
    ->where(['slug' => 'automatic_images'])
    ->first()?->value !== '0';
?>
<div class="form-card__hints">
    <h3><?= __('Automatic images') ?></h3>
    <?= $this->Form->create(null, ['url' => ['prefix' => 'Admin', 'plugin' => null, 'controller' => 'PlaceholderImages', 'action' => 'toggleAutomatic']]) ?>
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
