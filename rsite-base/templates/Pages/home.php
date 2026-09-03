<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Page $page
 */
use Cake\ORM\TableRegistry;

$this->assign('title', __($page->title));

$miniBanners = TableRegistry::getTableLocator()->get('Banners')
    ->find()
    ->where(['location' => 'home_mini', 'is_enabled' => true])
    ->orderBy(['id' => 'ASC'])
    ->limit(4)
    ->all();
?>
<div class="p-home">
    <div class="p-home__about-us">
        <div class="p-home__about-us-left">
            <h2 class="p-home__about-us-heading"><?= __('About ustest new script') ?></h2>
            <p class="p-home__about-us-text"><?= h($page->content['about_us_text'] ?? '') ?></p>
            <a class="p-home__about-us-cta" href="#"><?= __('More about the organisation') ?> &rarr;</a>
        </div>
        <div class="p-home__about-us-right">
            <?php foreach ($miniBanners as $miniBanner): ?>
                <?= $this->element('miniBanner', ['banner' => $miniBanner]) ?>
            <?php endforeach; ?>
        </div>
    </div>
    <div class="p-home__quick-access">  
        <?= $this->element('quickAccess', ['pageIds' => $page->content['quick_access'] ?? []]) ?>
    </div>
    <div class="p-home__news">
        <?= $this->element('news', ['pageIds' => $page->content['news'] ?? []]) ?>
    </div>
    <div class="p-home__fishing-grounds">
        <?= $this->element('fishing-grounds') ?>
    </div>
</div>