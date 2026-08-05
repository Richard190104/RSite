<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Page $page
 */
// $page->title is a fixed system label ("Home", "News", "Gallery"...), not
// admin-authored content, so it's translated like any other UI string. Since
// it's a dynamic __() call, `i18n extract` won't find it — the msgid has to
// be added to the .po files by hand whenever a new fixed page is created.
$this->assign('title', __($page->title));
?>
<pre><?= h(print_r($page->toArray(), true)) ?></pre>