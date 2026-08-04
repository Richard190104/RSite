<?php
/**
 * @var \App\View\AppView $this
 *
 * Renders the hero banner configured for the current page's location
 * (see Admin\BannersController). Controlled globally by Banner.enabled.
 */
use Cake\Core\Configure;
use Cake\ORM\TableRegistry;

if (!Configure::read('Banner.enabled', true)) {
    return;
}

$controller = strtolower((string)$this->getRequest()->getParam('controller'));
$pass = $this->getRequest()->getParam('pass') ?? [];
$location = $controller === 'pages' && ($pass[0] ?? null) === 'home' ? 'home' : $controller;

$banner = TableRegistry::getTableLocator()->get('Banners')
    ->find()
    ->where(['location' => $location])
    ->first();

if ($banner === null) {
    return;
}
?>
<div class="page-banner" style="background-image: url('<?= h($this->Url->build('/img/banners/' . $banner->background)) ?>');">
    <div class="page-banner__overlay">
        <h1 class="page-banner__title"><?= h($banner->title) ?></h1>
    </div>
</div>