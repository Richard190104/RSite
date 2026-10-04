<?php
/**
 * @var \App\View\AppView $this
 *
 * Footer shell: owns the column layout and this app's own data (homepage
 * quick-access page ids — not a Rcore table). Brand/contact/partners come
 * straight from Rcore\View\SiteInfoTrait / Rcore.Logos inside their own
 * elements, no params needed — see this plugin's README for the shape of
 * each.
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
?>
<div class="site-footer">
    <div class="site-footer__inner">
        <div class="site-footer__left">
            <?= $this->element('Rcore.Footer/brand') ?>
        </div>

        <div class="site-footer__center">
            <?= $this->element('Rcore.Footer/linkList', ['heading' => __('Quick access'), 'items' => $quickAccessItems]) ?>
        </div>

        <div class="site-footer__right">
            <?= $this->element('Rcore.Footer/contactList') ?>
        </div>

        <?= $this->element('Rcore.Footer/partners') ?>
    </div>
</div>
