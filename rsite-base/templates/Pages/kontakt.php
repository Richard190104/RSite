<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Page $page
 */
$this->assign('title', __($page->title));

$city = $this->city();
$address = $this->organisationAddress();
$email = $this->organisationEmail();
$ico = $this->organisationIco();
$facebookUrl = $this->facebookUrl();
$instagramUrl = $this->instagramUrl();
$mapQuery = trim($address !== '' ? $address : ($city !== '' ? $city : 'Medzilaborce'));
$mapEmbedUrl = 'https://www.google.com/maps?q=' . rawurlencode($mapQuery) . '&z=14&output=embed';
$mapLinkUrl = 'https://www.google.com/maps/search/?api=1&query=' . rawurlencode($mapQuery);
?>
<section class="p-contact">
    <div class="p-contact__body">
        <div class="p-contact__layout">
            <aside class="p-contact__info">
                <h2 class="p-contact__section-title"><?= __('Contact details') ?></h2>

                <ul class="p-contact__details">
                    <li class="p-contact__detail">
                        <span class="p-contact__detail-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M12 21s7-5.5 7-11a7 7 0 1 0-14 0c0 5.5 7 11 7 11Z"/>
                                <circle cx="12" cy="10" r="2.5"/>
                            </svg>
                        </span>
                        <div class="p-contact__detail-body">
                            <h3 class="p-contact__detail-title"><?= __('Address') ?></h3>
                            <?php if ($address !== ''): ?>
                                <p class="p-contact__detail-text p-contact__detail-text--multiline"><?= nl2br(h($address)) ?></p>
                            <?php else: ?>
                                <p class="p-contact__detail-text p-contact__detail-text--muted"><?= __('Address will appear here once set in admin.') ?></p>
                            <?php endif; ?>
                        </div>
                    </li>

                    <li class="p-contact__detail">
                        <span class="p-contact__detail-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="3" y="5" width="18" height="14" rx="2"/>
                                <path d="m3 7 9 6 9-6"/>
                            </svg>
                        </span>
                        <div class="p-contact__detail-body">
                            <h3 class="p-contact__detail-title"><?= __('Email') ?></h3>
                            <?php if ($email !== ''): ?>
                                <a class="p-contact__detail-link" href="mailto:<?= h($email) ?>"><?= h($email) ?></a>
                                <a class="p-contact__btn" href="mailto:<?= h($email) ?>"><?= __('Send an email') ?></a>
                            <?php else: ?>
                                <p class="p-contact__detail-text p-contact__detail-text--muted"><?= __('Email will appear here once set in admin.') ?></p>
                            <?php endif; ?>
                        </div>
                    </li>

                    <li class="p-contact__detail">
                        <span class="p-contact__detail-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="3" y="5" width="18" height="14" rx="2"/>
                                <path d="M3 9h18M8 13h4"/>
                            </svg>
                        </span>
                        <div class="p-contact__detail-body">
                            <h3 class="p-contact__detail-title"><?= __('ID No.') ?></h3>
                            <?php if ($ico !== ''): ?>
                                <p class="p-contact__detail-text"><?= h($ico) ?></p>
                            <?php else: ?>
                                <p class="p-contact__detail-text p-contact__detail-text--muted"><?= __('Company ID will appear here once set in admin.') ?></p>
                            <?php endif; ?>
                        </div>
                    </li>
                </ul>

                <div class="p-contact__social-block">
                    <h3 class="p-contact__social-heading"><?= __('Social media') ?></h3>
                    <p class="p-contact__social-lead">
                        <?= __('Follow our updates, events and catches.') ?>
                    </p>
                    <div class="p-contact__socials">
                        <?php if ($facebookUrl !== ''): ?>
                            <a class="p-contact__social p-contact__social--facebook" href="<?= h($facebookUrl) ?>" target="_blank" rel="noopener noreferrer">
                                Facebook
                            </a>
                        <?php else: ?>
                            <span class="p-contact__social p-contact__social--placeholder">Facebook</span>
                        <?php endif; ?>

                        <?php if ($instagramUrl !== ''): ?>
                            <a class="p-contact__social p-contact__social--instagram" href="<?= h($instagramUrl) ?>" target="_blank" rel="noopener noreferrer">
                                Instagram
                            </a>
                        <?php else: ?>
                            <span class="p-contact__social p-contact__social--placeholder">Instagram</span>
                        <?php endif; ?>
                    </div>
                </div>
            </aside>

            <div class="p-contact__map-block">
                <h2 class="p-contact__section-title"><?= __('Find us') ?></h2>
                <div class="p-contact__map">
                    <iframe
                        title="<?= h(__('Location map')) ?>"
                        loading="lazy"
                        referrerpolicy="no-referrer-when-downgrade"
                        src="<?= h($mapEmbedUrl) ?>"
                    ></iframe>
                </div>
                <a
                    class="p-contact__map-link"
                    href="<?= h($mapLinkUrl) ?>"
                    target="_blank"
                    rel="noopener noreferrer"
                >
                    <?= __('Open in Google Maps') ?> →
                </a>
            </div>
        </div>
    </div>
</section>
