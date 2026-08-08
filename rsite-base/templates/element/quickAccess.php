<?php
/**
 * @var \App\View\AppView $this
 * @var array<int, int> $pageIds
 *
 * Homepage "quick access" cards. The admin (Admin\PagesController::editHome())
 * only stores page ids in Page::$content['quick_access'], so everything shown
 * on a card is derived here:
 *  - label: the target page title (a fixed system label, so translated),
 *  - description: the target page's own content['description'], if it has one,
 *  - icon: matched on slug, with a neutral document icon as fallback.
 */
use Cake\ORM\TableRegistry;

$pageIds = array_values(array_filter(array_map('intval', (array)($pageIds ?? []))));

if (!$pageIds) {
    return;
}

$pages = TableRegistry::getTableLocator()->get('Pages')
    ->find()
    ->where(['Pages.id IN' => $pageIds])
    ->all()
    ->indexBy('id')
    ->toArray();


$icons = [
    'default' => '<rect x="4" y="3" width="16" height="18" rx="2"/><path d="M8 8h8M8 12h8M8 16h5"/>',
    'news' => '<path d="M4 5h12v14H6a2 2 0 0 1-2-2z"/><path d="M16 9h4v8a2 2 0 0 1-2 2"/><path d="M7 9h6M7 12.5h6M7 16h4"/>',
    'gallery' => '<rect x="3" y="6" width="18" height="14" rx="2"/><path d="M8.5 6 10 3.5h4L15.5 6"/><circle cx="12" cy="13" r="3.5"/>',
    'events' => '<rect x="3" y="5" width="18" height="16" rx="2"/><path d="M3 10h18M8 3v4M16 3v4"/>',
    'contact' => '<path d="M12 21s7-5.5 7-11a7 7 0 1 0-14 0c0 5.5 7 11 7 11"/><circle cx="12" cy="10" r="2.5"/>',
];
?>
<section class="quick-access">
    <span class="quick-access__heading"><?= __('Quick access') ?></span>

    <div class="quick-access__grid">
        <?php foreach ($pageIds as $pageId): ?>
            <?php
            $page = $pages[$pageId] ?? null;

            if ($page === null) {
                continue;
            }

            $description = $page->content['description'] ?? null;
            $icon = $icons[$page->slug] ?? $icons['default'];
            ?>
            <a class="quick-access__card" href="/<?= h($page->slug) ?>">
                <svg class="quick-access__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                    stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <?= $icon ?>
                </svg>
                <span class="quick-access__title"><?= h(__($page->title)) ?></span>
                <?php if ($description): ?>
                    <span class="quick-access__description"><?= h($description) ?></span>
                <?php endif; ?>
                <svg class="quick-access__arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                    stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M4 12h15M13 6l6 6-6 6"/>
                </svg>
            </a>
        <?php endforeach; ?>
    </div>
</section>
