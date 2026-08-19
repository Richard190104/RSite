<?php
/**
 * @var \App\View\AppView $this
 *
 * Homepage "Revíry a aktivity" section. Both the left photo and the right
 * feature cards are Banners rows under their own virtual locations (see
 * BannersTable::VIRTUAL_LOCATIONS), same pattern as the "about us" mini
 * banners: 'grounds-mini-main' is the single large image, 'grounds-mini'
 * are the up-to-4 cards (title + settings.subtitle + background as icon).
 */
use Cake\ORM\TableRegistry;

$Banners = TableRegistry::getTableLocator()->get('Banners');

$mainBanner = $Banners->find()
    ->where(['location' => 'grounds-mini-main', 'is_enabled' => true])
    ->orderBy(['id' => 'ASC'])
    ->first();

$cards = $Banners->find()
    ->where(['location' => 'grounds-mini', 'is_enabled' => true])
    ->orderBy(['id' => 'ASC'])
    ->limit(4)
    ->all();
?>
<section class="fishing-grounds">
    <h2 class="fishing-grounds__heading"><?= __('Fishing grounds and activities') ?></h2>

    <div class="fishing-grounds__layout">
        <?php if ($mainBanner !== null): ?>
            <div class="fishing-grounds__main" style="background-image: url('<?= h($this->Url->build('/img/banners/' . $mainBanner->background)) ?>')">
                <a class="fishing-grounds__main-cta" href="#"><?= __('View all fishing grounds') ?></a>
            </div>
        <?php endif; ?>

        <?php if ($cards->count()): ?>
            <div class="fishing-grounds__grid">
                <?php foreach ($cards as $card): ?>
                    <?php $subtitle = $card->settings['subtitle'] ?? ''; ?>
                    <div class="fishing-grounds__card">
                        <img class="fishing-grounds__icon" src="<?= h($this->Url->build('/img/banners/' . $card->background)) ?>" alt="">
                        <span class="fishing-grounds__text">
                            <span class="fishing-grounds__title"><?= h($card->title) ?></span>
                            <?php if ($subtitle !== ''): ?>
                                <p class="fishing-grounds__description"><?= h($subtitle) ?></p>
                            <?php endif; ?>
                            <?php // TODO: point at a real target once each card has one ?>
                            <a class="fishing-grounds__link" href="#"><?= __('More information') ?> &rarr;</a>
                        </span>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>
