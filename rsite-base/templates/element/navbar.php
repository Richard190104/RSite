<?php
/**
 * @var \App\View\AppView $this
 *
 */
use Cake\ORM\TableRegistry;

$navbarCategories = TableRegistry::getTableLocator()->get('NavbarCategories')
    ->find()
    ->contain(['Pages'])
    ->orderBy(['NavbarCategories.title' => 'ASC'])
    ->all();

$Texts = TableRegistry::getTableLocator()->get('Texts');
$organisationName = $Texts->value('Organisation Name');
$city = $Texts->value('City');

$logoPath = TableRegistry::getTableLocator()->get('Logos')->path('Main logo');

$contactPage = TableRegistry::getTableLocator()->get('Pages')
    ->find()
    ->select(['title', 'slug'])
    ->where(['slug' => 'kontakt'])
    ->first();
?>
<nav class="site-nav">
    <div class="site-nav__identity">
        <div class="site-nav__identity-inner">
            <a class="site-nav__brand" href="<?= $this->Url->build('/') ?>">
                <?php if ($city !== ''): ?>
                    <span class="site-nav__city"><?= h(__('Local organisation')) ?> <?= h($city) ?></span>
                <?php endif; ?>
                <span class="site-nav__org"><?= h($organisationName) ?></span>
            </a>
        </div>
    </div>

    <div class="site-nav__menubar">
        <div class="site-nav__menubar-inner">
            <a class="site-nav__logo" href="<?= $this->Url->build('/') ?>">
                <?php if ($logoPath !== ''): ?>
                    <img src="<?= h($this->Url->build('/img/logos/' . $logoPath)) ?>" alt="<?= h($organisationName) ?>">
                <?php else: ?>
                    <svg viewBox="0 0 32 32" fill="none" aria-hidden="true">
                        <path d="M4 16c3.5-5 8-7.5 13-7.5 3.6 0 6.6 1.4 8.8 3.4l3.2-2.4v13l-3.2-2.4c-2.2 2-5.2 3.4-8.8 3.4-5 0-9.5-2.5-13-7.5Z" fill="#143a6b"/>
                        <circle cx="12" cy="14.4" r="1.3" fill="#f9d71c"/>
                    </svg>
                <?php endif; ?>
            </a>

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