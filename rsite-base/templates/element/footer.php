<?php
/**
 * @var \App\View\AppView $this
 *
 * Organisation name/city/logo come from AppView::SiteInfoTrait — same
 * source navbar.php uses, so both stay in sync from one place.
 */
use Cake\ORM\TableRegistry;

$organisationName = $this->organisationName();
$city = $this->city();
$logoPath = $this->logoPath();
$description = $this->description();
$organisationAddress = $this->organisationAddress();
$organisationEmail = $this->organisationEmail();
$organisationIco = $this->organisationIco();

// Same page ids as the homepage's own quick-access grid (set via
// Admin\PagesController::editHome()) — plain text links here rather than
// reusing the quickAccess element's icon/card markup, which is sized for
// the homepage, not a footer column.
$quickAccessPageIds = $this->quickAccessPageIds();
$quickAccessPages = $quickAccessPageIds
    ? TableRegistry::getTableLocator()->get('Pages')
        ->find()
        ->select(['id', 'title', 'slug'])
        ->where(['id IN' => $quickAccessPageIds])
        ->all()
        ->indexBy('id')
        ->toArray()
    : [];
?>
<div class="site-footer">
    <div class="site-footer__inner">
    <div class="site-footer__left">
        <a class="site-footer__brand" href="<?= $this->Url->build('/') ?>">
            <span class="site-footer__logo">
                <?php if ($logoPath !== ''): ?>
                    <img src="<?= h($this->Url->build('/img/logos/' . $logoPath)) ?>" alt="<?= h($organisationName) ?>">
                <?php else: ?>
                    <svg viewBox="0 0 32 32" fill="none" aria-hidden="true">
                        <path d="M4 16c3.5-5 8-7.5 13-7.5 3.6 0 6.6 1.4 8.8 3.4l3.2-2.4v13l-3.2-2.4c-2.2 2-5.2 3.4-8.8 3.4-5 0-9.5-2.5-13-7.5Z" fill="#143a6b"/>
                        <circle cx="12" cy="14.4" r="1.3" fill="#f9d71c"/>
                    </svg>
                <?php endif; ?>
            </span>
            <span class="site-footer__text">

                <span class="site-footer__org">Slovenský rybársky zväz</span>
                <span class="site-footer__city"><?= h($city) ?></span>
                <span class="site-footer__description"><?= h($description) ?></span>
            </span>
        </a>
    </div>

    <div class="site-footer__center">
        <?php if ($quickAccessPages): ?>
            <span class="site-footer__heading"><?= __('Quick access') ?></span>
            <ul class="site-footer__links">
                <?php foreach ($quickAccessPageIds as $pageId): ?>
                    <?php $page = $quickAccessPages[$pageId] ?? null; ?>
                    <?php if ($page !== null): ?>
                        <li><a href="/<?= h($page->slug) ?>"><?= h(__($page->title)) ?></a></li>
                    <?php endif; ?>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>

    <div class="site-footer__right">
        <?php if ($organisationAddress !== '' || $organisationEmail !== '' || $organisationIco !== ''): ?>
            <span class="site-footer__heading"><?= __('Contact') ?></span>
            <ul class="site-footer__contact">
                <?php if ($organisationAddress !== ''): ?>
                    <li class="site-footer__contact-item">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M12 21s7-5.5 7-11a7 7 0 1 0-14 0c0 5.5 7 11 7 11Z"/>
                            <circle cx="12" cy="10" r="2.5"/>
                        </svg>
                        <span><?= h($organisationAddress) ?></span>
                    </li>
                <?php endif; ?>
                <?php if ($organisationIco !== ''): ?>
                    <li class="site-footer__contact-item">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <rect x="3" y="5" width="18" height="14" rx="2"/>
                            <path d="M3 9h18M8 13h4"/>
                        </svg>
                        <span><?= __('ID No.') ?> <?= h($organisationIco) ?></span>
                    </li>
                <?php endif; ?>
                <?php if ($organisationEmail !== ''): ?>
                    <li class="site-footer__contact-item">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <rect x="3" y="5" width="18" height="14" rx="2"/>
                            <path d="m3 7 9 6 9-6"/>
                        </svg>
                        <span><?= h($organisationEmail) ?></span>
                    </li>
                <?php endif; ?>
            </ul>
        <?php endif; ?>

    </div>
    </div>
</div>
