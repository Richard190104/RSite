<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Page $page
 * @var \App\Model\Entity\Banner|null $mainBanner
 * @var iterable<\App\Model\Entity\CommitteeMember> $committeeMembers
 */
$this->assign('title', __($page->title));
$aboutUsText = $page->content['about_us_text'] ?? '';
?>
<section class="p-onas">
    <div class="p-onas__layout">
        <div class="p-onas__text">
            <h1 class="p-onas__heading"><?= __('About our organisation') ?></h1>
            <p class="p-onas__body"><?= h($aboutUsText) ?></p>
        </div>
        <?php if ($mainBanner !== null): ?>
            <div
                class="p-onas__image"
                style="background-image: url('<?= h($this->Url->build('/img/banners/' . $mainBanner->background)) ?>')"
            ></div>
        <?php endif; ?>
    </div>
</section>
<?= $this->element('committee', ['committeeMembers' => $committeeMembers]) ?>

<h1 style="color: #f40707; font-size: 2.5rem; margin-bottom: 1rem; font-weight: 700; text-align: center; width: 100%;">TODO - naše aktivity - ked sa dokončí aktivity page</h1>
