<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Page $page
 */
$this->assign('title', __($page->title));

$city = $this->city();
$address = $this->organisationAddress();
$email = $this->organisationEmail();
$phone = $this->phone();
$ico = $this->organisationIco();
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
                            <?php endif; ?>
                        </div>
                    </li>

                    <li class="p-contact__detail">
                        <span class="p-contact__detail-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92Z"/>
                            </svg>
                        </span>
                        <div class="p-contact__detail-body">
                            <h3 class="p-contact__detail-title"><?= __('Phone') ?></h3>
                            <?php if ($phone !== ''): ?>
                                <a class="p-contact__detail-link" href="tel:<?= h($phone) ?>"><?= h($phone) ?></a>
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
                            <?php endif; ?>
                        </div>
                    </li>
                </ul>
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
