<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Page $page
 * @var \App\Model\Entity\Banner|null $mainBanner
 * @var iterable<\App\Model\Entity\CommitteeMember> $committeeMembers
 * @var array<\App\Model\Entity\Event> $featuredActivities
 */
$this->assign('title', __($page->title));
$aboutUsText = $page->content['about_us_text'] ?? '';

// Same generic icon set as templates/Pages/aktivity.php's category tiles —
// not meant to mean anything specific per activity, just a bit of visual
// variety down the timeline, assigned by position rather than admin choice
// (see $activityIcons there for the shared rationale).
$activityIcons = [
    '<path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>',
    '<path d="M6 9H4.5a2.5 2.5 0 0 1 0-5H6"/><path d="M18 9h1.5a2.5 2.5 0 0 0 0-5H18"/><path d="M4 22h16"/><path d="M10 14.66V17c0 .55-.47.98-.97 1.21C7.85 18.75 7 20.24 7 22"/><path d="M14 14.66V17c0 .55.47.98.97 1.21C16.15 18.75 17 20.24 17 22"/><path d="M18 2H6v7a6 6 0 0 0 12 0V2Z"/>',
    '<path d="M12 22c4-4 8-7.5 8-12a8 8 0 1 0-16 0c0 4.5 4 8 8 12Z"/><circle cx="12" cy="10" r="3"/>',
    '<path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/>',
    '<rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/>',
];
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

<?php if ($featuredActivities): ?>
    <section class="p-onas__timeline-section">
        <h2 class="p-onas__timeline-heading"><?= __('Our activities') ?></h2>
        <div class="p-onas__timeline">
            <?php foreach ($featuredActivities as $index => $event): ?>
                <?php
                    $categoryLink = ($event->category && $event->category->show_in_gallery)
                        ? $this->Url->build(['controller' => 'Gallery', 'action' => 'category', $event->category->id])
                        : null;
                    $itemTag = $categoryLink !== null ? 'a' : 'div';
                ?>
                <<?= $itemTag ?>
                    class="p-onas__timeline-item"
                    <?php if ($categoryLink !== null): ?>
                        href="<?= h($categoryLink) ?>"
                    <?php endif; ?>
                >
                    <span class="p-onas__timeline-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <?= $activityIcons[$index % count($activityIcons)] ?>
                        </svg>
                    </span>
                    <span class="p-onas__timeline-date"><?= $event->date ? h($event->date->i18nFormat('MMM yyyy')) : '' ?></span>
                    <span class="p-onas__timeline-title"><?= h($event->title) ?></span>
                </<?= $itemTag ?>>
            <?php endforeach; ?>
        </div>
    </section>
<?php endif; ?>
