<?php
/**
 * @var \App\View\AppView $this
 *
 * Renders the hero banner(s) configured for the current page's location
 * (see Admin\BannersController). Whether a banner shows is a per-banner
 * setting (Banner::is_enabled), not a global switch — a location can have
 * more than one enabled banner, in which case they're shown as a Swiper
 * carousel with dot pagination; a single banner renders the same markup
 * with the carousel JS never engaging (Swiper with one slide is static).
 */
use Cake\ORM\TableRegistry;

$controller = strtolower((string)$this->getRequest()->getParam('controller'));
$action = strtolower((string)$this->getRequest()->getParam('action'));
$pass = $this->getRequest()->getParam('pass') ?? [];

if ($controller === 'pages' && $action === 'home') {
    $location = 'home';
} elseif ($controller === 'pages' && $action === 'kontakt') {
    $location = 'kontakt';
} elseif ($controller === 'pages' && $action === 'aktivity') {
    $location = 'aktivity';
} elseif ($controller === 'pages' && ($pass[0] ?? null) !== null) {
    $location = (string)$pass[0];
} else {
    $location = $controller;
}
$banners = TableRegistry::getTableLocator()->get('Banners')
    ->find()
    ->where(['location' => $location, 'is_enabled' => true])
    ->orderBy(['id' => 'ASC'])
    ->all()
    ->toArray();

if (!$banners) {
    return;
}
?>
<?= $this->Html->css('vendor/swiper-bundle.min') ?>
<div class="page-banner swiper">
    <div class="swiper-wrapper">
        <?php foreach ($banners as $banner): ?>
            <?php $subtitle = $banner->settings['subtitle'] ?? ''; ?>
            <div class="page-banner__slide swiper-slide" style="background-image: url('<?= h($this->Url->build('/img/banners/' . $banner->background)) ?>');">
                <div class="page-banner__overlay">
                    <div class="page-banner__content">
                        <h1 class="page-banner__title"><?= h($banner->title) ?></h1>
                        <?php if ($subtitle !== ''): ?>
                            <p class="page-banner__subtitle"><?= h($subtitle) ?></p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    <?php if (count($banners) > 1): ?>
        <div class="swiper-pagination"></div>
    <?php endif; ?>
</div>
<?php if (count($banners) > 1): ?>
    <?= $this->Html->script('vendor/swiper-bundle.min') ?>
    <?= $this->Html->script('banner-swiper') ?>
<?php endif; ?>
