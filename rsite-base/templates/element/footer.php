<?php
/**
 * @var \App\View\AppView $this
 *
 * Builds each slot's content as its own variable (usually by calling one of
 * this plugin's other Footer/* elements, but any app-specific markup works
 * just as well), then hands all four to Rcore.Footer/layout, which owns the
 * actual column shell/arrangement. This app's only genuinely own data here
 * is the homepage's quick-access page ids (not a Rcore table) — brand/
 * contact/partners pull everything they need from Rcore\View\SiteInfoTrait/
 * Rcore.Logos themselves.
 */
use Cake\ORM\TableRegistry;

$quickAccessPageIds = $this->quickAccessPageIds();
$quickAccessPages = $quickAccessPageIds
    ? TableRegistry::getTableLocator()->get('Pages')
        ->find()
        ->select(['id', 'title', 'slug'])
        ->where(['id IN' => $quickAccessPageIds])
        ->all()
        ->indexBy('id')
        ->toArray()
    : [];

$quickAccessItems = [];
foreach ($quickAccessPageIds as $pageId) {
    $page = $quickAccessPages[$pageId] ?? null;
    if ($page !== null) {
        $quickAccessItems[] = ['url' => '/' . $page->slug, 'label' => __($page->title)];
    }
}

$left = $this->element('Rcore.Footer/brand');
$center = $this->element('Rcore.Footer/linkList', ['heading' => __('Quick access'), 'items' => $quickAccessItems]);
$right = $this->element('Rcore.Footer/contactList');
$partners = $this->element('Rcore.Footer/partners');
?>
<?= $this->element('Rcore.Footer/layout', [
    'left' => $left,
    'center' => $center,
    'right' => $right,
    'partners' => $partners,
]) ?>
