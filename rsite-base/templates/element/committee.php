<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\CommitteeMember> $committeeMembers
 *
 * "Výbor organizácie" section(s) — one card per committee member (name and
 * section required, role/phone/email/photo optional), grouped into one
 * block per distinct 'section' value (e.g. "Členovia výboru", "Kontrolná
 * komisia") — each rendered with its own heading and full-width background,
 * in whatever order the groups first appear in $committeeMembers. Every
 * other block gets committee-wrap--alt so the background alternates
 * white/main-bg down the page. Used on the "O nás" page.
 */
$sections = [];
foreach ($committeeMembers as $member) {
    $sections[$member->section][] = $member;
}
?>
<?php $sectionIndex = 0; ?>
<?php foreach ($sections as $sectionName => $members): ?>
    <?php $sectionIndex++; ?>
    <div class="committee-wrap<?= $sectionIndex % 2 === 0 ? ' committee-wrap--alt' : '' ?>">
        <section class="committee">
            <h2 class="committee__heading"><?= h($sectionName) ?></h2>

            <div class="committee__grid">
                <?php foreach ($members as $member): ?>
                    <div
                        class="committee__card"
                        data-committee-member
                        data-name="<?= h($member->name) ?>"
                        data-role="<?= h($member->role ?? '') ?>"
                        data-email="<?= h($member->email ?? '') ?>"
                        data-phone="<?= h($member->phone ?? '') ?>"
                        data-photo="<?= $member->photo ? h($this->Url->build('/img/committee/' . $member->photo)) : '' ?>"
                    >
                        <div class="committee__photo">
                            <?php if ($member->photo): ?>
                                <?= $this->Html->image('/img/committee/' . $member->photo, ['alt' => $member->name]) ?>
                            <?php else: ?>
                                <svg class="committee__photo-placeholder" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <circle cx="12" cy="8" r="4"/>
                                    <path d="M4 20c0-4.4 3.6-8 8-8s8 3.6 8 8"/>
                                </svg>
                            <?php endif; ?>
                        </div>
                        <div class="committee__info">
                            <span class="committee__name"><?= h($member->name) ?></span>
                            <?php if ($member->role): ?>
                                <span class="committee__role"><?= h($member->role) ?></span>
                            <?php endif; ?>
                            <?php if ($member->email): ?>
                                <a class="committee__email" href="mailto:<?= h($member->email) ?>"><?= h($member->email) ?></a>
                            <?php endif; ?>
                            <?php if ($member->phone): ?>
                                <a class="committee__phone" href="tel:<?= h(preg_replace('/\s+/', '', $member->phone)) ?>"><?= h($member->phone) ?></a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
    </div>
<?php endforeach; ?>

<div class="committee-modal" data-committee-modal>
    <div class="committee-modal__frame">
        <button type="button" class="committee-modal__close" aria-label="<?= __('Close') ?>">&times;</button>
        <div class="committee-modal__photo">
            <img class="committee-modal__image" alt="" hidden>
            <svg class="committee-modal__photo-placeholder" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <circle cx="12" cy="8" r="4"/>
                <path d="M4 20c0-4.4 3.6-8 8-8s8 3.6 8 8"/>
            </svg>
        </div>
        <span class="committee-modal__name"></span>
        <span class="committee-modal__role" hidden></span>
        <a class="committee-modal__email" href="" hidden></a>
        <a class="committee-modal__phone" href="" hidden></a>
    </div>
</div>
<?= $this->Html->script('committee-modal') ?>
