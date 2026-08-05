<?php
/**
 * @var \App\View\AppView $this
 *
 * Site-wide navbar: fetches its own data (NavbarCategories -> Pages) since
 * it renders on every public page via the shared layout, not just one
 * controller action.
 */
use Cake\ORM\TableRegistry;

$navbarCategories = TableRegistry::getTableLocator()->get('NavbarCategories')
    ->find()
    ->contain(['Pages'])
    ->orderBy(['NavbarCategories.title' => 'ASC'])
    ->all();
?>
<nav class="site-nav">
    <div class="site-nav__brand">
        <a href="<?= $this->Url->build('/') ?>">MO SRZ Medzilaborce</a>
    </div>
    <ul class="site-nav__categories">
        <?php foreach ($navbarCategories as $category): ?>
            <li class="site-nav__category">
                <span><?= h($category->title) ?></span>
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
    </ul>
</nav>