<?php
/**
 * @var \App\View\AppView $this
 *
 * Site-wide navbar: fetches its own NavbarCategories/Pages data since it
 * renders on every public page via the shared layout, not just one
 * controller action. Organisation name/city/logo/contact page come from
 * AppView::SiteInfoTrait, shared with any other element that needs them
 * (e.g. footer.php) so the lookup logic and per-request caching live in
 * one place instead of being copied into every element.
 *
 * Two-row layout: a navy top row (brand + notifications) and a white
 * bottom row (category menu) — modeled after a reference two-tier
 * navbar, restyled to the site's own navy/yellow palette instead of
 * introducing a new brand color.
 */
use Cake\ORM\TableRegistry;

$navbarCategories = TableRegistry::getTableLocator()->get('NavbarCategories')
    ->find()
    ->contain([
        'Pages' => function ($q) {
            return $q->orderBy(['Pages.position' => 'ASC']);
        },
    ])
    ->orderBy(['NavbarCategories.position' => 'ASC'])
    ->all();

$organisationName = $this->organisationName();
$city = $this->city();
$logoPath = $this->logoPath();
$contactPage = $this->contactPage();
$activeNotifications = $this->activeNotifications();
?>
<nav class="site-nav">
    <div class="site-nav__top">
        <div class="site-nav__top-inner">
            <a class="site-nav__brand" href="<?= $this->Url->build('/') ?>">
                <span class="site-nav__logo">
                    <?php if ($logoPath !== ''): ?>
                        <img src="<?= h($this->Url->build('/img/logos/' . $logoPath)) ?>" alt="<?= h($organisationName) ?>">
                    <?php else: ?>
                        <svg viewBox="0 0 32 32" fill="none" aria-hidden="true">
                            <path d="M4 16c3.5-5 8-7.5 13-7.5 3.6 0 6.6 1.4 8.8 3.4l3.2-2.4v13l-3.2-2.4c-2.2 2-5.2 3.4-8.8 3.4-5 0-9.5-2.5-13-7.5Z" fill="#143a6b"/>
                            <circle cx="12" cy="14.4" r="1.3" fill="#f9d71c"/>
                        </svg>
                    <?php endif; ?>
                </span>
                <span class="site-nav__brand-text">
                    <?php if ($city !== ''): ?>
                        <span class="site-nav__city"><?= h($city) ?></span>
                    <?php endif; ?>
                    <span class="site-nav__org"><?= h($organisationName) ?></span>
                </span>
            </a>

            <div class="site-nav__news-icon">
                <button type="button" class="site-nav__notifications-toggle" aria-label="<?= __('Notifications') ?>" aria-expanded="false">
                    <span class="site-nav__notifications-label<?= $activeNotifications ? ' site-nav__notifications-label--has-active' : '' ?>"><?= __('Notifications') ?></span>
                    <img src="<?= h($this->Url->build('/img/icons/newsIcon.svg')) ?>" alt="">
                    <?php if ($activeNotifications): ?>
                        <span class="site-nav__notifications-badge"><?= count($activeNotifications) ?></span>
                    <?php endif; ?>
                </button>

                <div class="site-nav__notifications-panel">
                    <span class="site-nav__notifications-title"><?= __('Notifications') ?></span>

                    <?php if ($activeNotifications): ?>
                        <ul class="site-nav__notifications-list">
                            <?php foreach ($activeNotifications as $notification): ?>
                                <li class="site-nav__notification">
                                    <img src="<?= h($this->Url->build('/img/notifications/' . $notification->image)) ?>" alt="">
                                    <div class="site-nav__notification-text">
                                        <span class="site-nav__notification-title"><?= h($notification->title) ?></span>
                                        <p class="site-nav__notification-description"><?= h($notification->description) ?></p>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <div class="site-nav__notifications-empty">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <circle cx="12" cy="12" r="9"/>
                                <path d="M12 11v5"/>
                                <path d="M12 8h.01"/>
                            </svg>
                            <p><?= __('No active notifications.') ?></p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="site-nav__menubar">
        <div class="site-nav__menubar-inner">
            <ul class="site-nav__categories">
                <?php foreach ($navbarCategories as $category): ?>
                    <li class="site-nav__category">
                        <span tabindex="0"><?= h($category->title) ?></span>
                        <?php if (!empty($category->pages)): ?>
                            <ul class="site-nav__dropdown">
                                <?php foreach ($category->pages as $page): ?>
                                    <li>
                                        <a href="/<?= h($page->slug) ?>"><?= h(__($page->title)) ?></a>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>

                <?php if ($contactPage !== null): ?>
                    <li class="site-nav__category site-nav__category--end">
                        <a href="/<?= h($contactPage->slug) ?>"><?= h(__($contactPage->title)) ?></a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>
<div class="site-nav__notifications-overlay"></div>
<?= $this->Html->script('navbar-notifications') ?>
