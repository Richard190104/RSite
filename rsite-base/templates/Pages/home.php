<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Page $page
 */

$this->assign('title', __($page->title));
?>
<div class="p-home">
    <div class="p-home__quick-access">  
        <?= $this->element('quickAccess', ['pageIds' => $page->content['quick_access'] ?? []]) ?>
    </div>
</div>