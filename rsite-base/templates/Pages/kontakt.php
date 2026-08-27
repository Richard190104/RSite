<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Page $page
 */
$this->assign('title', __($page->title));

$organisationName = $this->organisationName();
$city = $this->city();
$address = $this->organisationAddress();
$email = $this->organisationEmail();
$ico = $this->organisationIco();
$facebookUrl = $this->facebookUrl();
$instagramUrl = $this->instagramUrl();
?>
<section class="p-contact">
    <div class="p-contact__hero">
        <div class="p-contact__hero-inner">
            <p class="p-contact__eyebrow"><?= h($organisationName !== '' ? $organisationName : __('Contact')) ?></p>
            <h1 class="p-contact__title"><?= h(__($page->title)) ?></h1>
            <p class="p-contact__lead">
                <?= __('Write to us, stop by, or follow what we are up to. We are happy to hear from members and visitors alike.') ?>
            </p>
        </div>
    </div>

    <div class="p-contact__body">
        <div class="p-contact__grid">
            <article class="p-contact__card">
                <span class="p-contact__card-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 21s7-5.5 7-11a7 7 0 1 0-14 0c0 5.5 7 11 7 11Z"/>
                        <circle cx="12" cy="10" r="2.5"/>
                    </svg>
                </span>
                <h2 class="p-contact__card-title"><?= __('Address') ?></h2>
                <?php if ($address !== ''): ?>
                    <p class="p-contact__card-text p-contact__card-text--multiline"><?= nl2br(h($address)) ?></p>
                <?php else: ?>
                    <p class="p-contact__card-text p-contact__card-text--muted"><?= __('Address will appear here once set in admin.') ?></p>
                <?php endif; ?>
            </article>

            <article class="p-contact__card">
                <span class="p-contact__card-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="5" width="18" height="14" rx="2"/>
                        <path d="m3 7 9 6 9-6"/>
                    </svg>
                </span>
                <h2 class="p-contact__card-title"><?= __('Email') ?></h2>
                <?php if ($email !== ''): ?>
                    <a class="p-contact__card-link" href="mailto:<?= h($email) ?>"><?= h($email) ?></a>
                    <a class="p-contact__btn" href="mailto:<?= h($email) ?>"><?= __('Send an email') ?></a>
                <?php else: ?>
                    <p class="p-contact__card-text p-contact__card-text--muted"><?= __('Email will appear here once set in admin.') ?></p>
                <?php endif; ?>
            </article>

            <article class="p-contact__card">
                <span class="p-contact__card-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="5" width="18" height="14" rx="2"/>
                        <path d="M3 9h18M8 13h4"/>
                    </svg>
                </span>
                <h2 class="p-contact__card-title"><?= __('ID No.') ?></h2>
                <?php if ($ico !== ''): ?>
                    <p class="p-contact__card-text"><?= h($ico) ?></p>
                <?php else: ?>
                    <p class="p-contact__card-text p-contact__card-text--muted"><?= __('Company ID will appear here once set in admin.') ?></p>
                <?php endif; ?>
                <?php if ($city !== ''): ?>
                    <p class="p-contact__card-meta"><?= h($city) ?></p>
                <?php endif; ?>
            </article>
        </div>

        <div class="p-contact__panels">
            <section class="p-contact__panel">
                <h2 class="p-contact__panel-title"><?= __('Social media') ?></h2>
                <p class="p-contact__panel-text">
                    <?= __('Follow our updates, events and catches. Links can be filled in under Texts in admin.') ?>
                </p>
                <div class="p-contact__socials">
                    <?php if ($facebookUrl !== ''): ?>
                        <a class="p-contact__social p-contact__social--facebook" href="<?= h($facebookUrl) ?>" target="_blank" rel="noopener noreferrer">
                            <span class="p-contact__social-label">Facebook</span>
                            <span class="p-contact__social-hint"><?= __('Open page') ?></span>
                        </a>
                    <?php else: ?>
                        <div class="p-contact__social p-contact__social--placeholder">
                            <span class="p-contact__social-label">Facebook</span>
                            <span class="p-contact__social-hint"><?= __('Coming soon') ?></span>
                        </div>
                    <?php endif; ?>

                    <?php if ($instagramUrl !== ''): ?>
                        <a class="p-contact__social p-contact__social--instagram" href="<?= h($instagramUrl) ?>" target="_blank" rel="noopener noreferrer">
                            <span class="p-contact__social-label">Instagram</span>
                            <span class="p-contact__social-hint"><?= __('Open profile') ?></span>
                        </a>
                    <?php else: ?>
                        <div class="p-contact__social p-contact__social--placeholder">
                            <span class="p-contact__social-label">Instagram</span>
                            <span class="p-contact__social-hint"><?= __('Coming soon') ?></span>
                        </div>
                    <?php endif; ?>
                </div>
            </section>

            <section class="p-contact__panel p-contact__panel--map">
                <h2 class="p-contact__panel-title"><?= __('Find us') ?></h2>
                <p class="p-contact__panel-text">
                    <?= __('Medzilaborce — visit us at the address above.') ?>
                </p>
                <div class="p-contact__map">
                    <iframe
                        title="<?= h(__('Map of Medzilaborce')) ?>"
                        loading="lazy"
                        referrerpolicy="no-referrer-when-downgrade"
                        src="https://www.openstreetmap.org/export/embed.html?bbox=21.88%2C49.24%2C21.95%2C49.28&amp;layer=mapnik&amp;marker=49.261%2C21.904"
                    ></iframe>
                </div>
                <a
                    class="p-contact__map-link"
                    href="https://www.openstreetmap.org/?mlat=49.261&amp;mlon=21.904#map=14/49.261/21.904"
                    target="_blank"
                    rel="noopener noreferrer"
                >
                    <?= __('Open larger map') ?> →
                </a>
            </section>
        </div>
    </div>
</section>
