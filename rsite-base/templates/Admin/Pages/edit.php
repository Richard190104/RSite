<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Page $page
 */
$this->assign('title', h(__($page->title)));
?>
<div class="content">
    <h1><?= h(__($page->title)) ?></h1>
    <p><?= __('No page-specific settings are defined for this page yet.') ?></p>
</div>