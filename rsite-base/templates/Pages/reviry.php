<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Page $page
 * @var array<\App\Model\Entity\FishingGround> $fishingGrounds
 */
$this->assign('title', __($page->title));
?>
<section class="p-reviry">

    <h2 class="p-reviry__map-heading"><?= __('Our fishing grounds') ?></h2>

    <?php if (!$fishingGrounds): ?>
        <p class="p-reviry__empty"><?= __('No fishing grounds have been added yet.') ?></p>
    <?php else: ?>
        <div class="p-reviry__grid">
            <?php foreach ($fishingGrounds as $fishingGround): ?>
                <article
                    class="p-reviry__card"
                    data-reviry-card
                    data-id="<?= h($fishingGround->id) ?>"
                    data-title="<?= h($fishingGround->title) ?>"
                    data-description="<?= h($fishingGround->description ?? '') ?>"
                    data-location="<?= h($fishingGround->location ?? '') ?>"
                    data-type="<?= h($fishingGround->type ?? '') ?>"
                    data-registration-number="<?= h($fishingGround->registration_number ?? '') ?>"
                    data-image="<?= $fishingGround->image ? h($this->Url->build('/img/fishing-grounds/' . $fishingGround->image)) : '' ?>"
                >
                    <div
                        class="p-reviry__image<?= $fishingGround->image ? '' : ' p-reviry__image--placeholder' ?>"
                        <?php if ($fishingGround->image): ?>
                            style="background-image: url('<?= h($this->Url->build('/img/fishing-grounds/' . $fishingGround->image)) ?>')"
                        <?php endif; ?>
                    >
                        <?php if ($fishingGround->registration_number): ?>
                            <span class="p-reviry__badge"><?= h($fishingGround->registration_number) ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="p-reviry__body">
                        <?php if ($fishingGround->type): ?>
                            <span class="p-reviry__type"><?= h(mb_strtoupper($fishingGround->type)) ?></span>
                        <?php endif; ?>
                        <h2 class="p-reviry__title"><?= h($fishingGround->title) ?></h2>
                        <?php if ($fishingGround->description): ?>
                            <p class="p-reviry__description"><?= h($fishingGround->description) ?></p>
                        <?php endif; ?>
                        <hr class="p-reviry__divider">
                        <span class="p-reviry__link"><?= __('Fishing ground detail') ?> &rarr;</span>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <div class="p-reviry__map-section">
        <div class="p-reviry__map-section-inner">
            <h1 class="p-reviry__map-heading"><?= __('Fishing grounds map') ?></h1>
            <?= $this->element('reviryMap', ['fishingGrounds' => $fishingGrounds]) ?>
        </div>
    </div>
</section>

<div class="p-reviry__modal" data-reviry-modal>
    <div class="p-reviry__modal-frame">
        <?= $this->element('modalCloseBtn', ['extraClass' => 'p-reviry__modal-close']) ?>
        <img class="p-reviry__modal-image" alt="" hidden>
        <div class="p-reviry__modal-body">
            <div class="p-reviry__modal-meta-row">
                <span class="p-reviry__modal-type" hidden></span>
                <span class="p-reviry__modal-registration" hidden></span>
            </div>
            <h3 class="p-reviry__modal-title"></h3>
            <p class="p-reviry__modal-location" hidden>
                <svg class="p-reviry__modal-location-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 21s7-5.5 7-11a7 7 0 1 0-14 0c0 5.5 7 11 7 11Z"/><circle cx="12" cy="10" r="3"></circle></svg>
                <span class="p-reviry__modal-location-text"></span>
            </p>
            <hr class="p-reviry__modal-divider">
            <p class="p-reviry__modal-description"></p>
        </div>
    </div>
</div>

<?= $this->Html->script('reviry-modal') ?>
