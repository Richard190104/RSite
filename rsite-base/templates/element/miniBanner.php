<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Banner $banner
 *
 * One homepage "about us" feature tile — see Admin\BannersController for how
 * these are managed (location = 'home_mini'). The banner's own 'background'
 * upload is used as the tile's icon image, not a cover photo — these tiles
 * are small, so a normal fitted <img> is what the design calls for.
 */
$subtitle = $banner->settings['subtitle'] ?? '';
?>
<div class="mini-banner">
    <img class="mini-banner__icon" src="<?= h($this->Url->build('/img/banners/' . $banner->background)) ?>" alt="">
    <span class="mini-banner__title"><?= h($banner->title) ?></span>
    <?php if ($subtitle !== ''): ?>
        <span class="mini-banner__subtitle"><?= h($subtitle) ?></span>
    <?php endif; ?>
</div>
