<?php
/**
 * @var \App\View\AppView $this
 *
 * Renders the hero banner configured for the current page's location
 * (see Admin\BannersController). Whether a banner shows is a per-banner
 * setting (Banner::is_enabled), not a global switch.
 */
use Cake\ORM\TableRegistry;

$controller = strtolower((string)$this->getRequest()->getParam('controller'));
$pass = $this->getRequest()->getParam('pass') ?? [];
$location = $controller === 'pages' && ($pass[0] ?? null) === 'home' ? 'home' : $controller;

$banner = TableRegistry::getTableLocator()->get('Banners')
    ->find()
    ->where(['location' => $location, 'is_enabled' => true])
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