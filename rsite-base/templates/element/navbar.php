<?php
/**
 * @var \App\View\AppView $this
 *
 * Site-wide navbar shell: fetches this app's own data (NavbarCategories/Pages,
 * logo, contact page, notifications — none of these tables live in Rcore)
 * and composes the three shared Rcore\templates\element\Navbar\* elements
 * into it. Organisation name/city come from Rcore\View\SiteInfoTrait inside
 * those elements themselves, not from here.
 *
 * Two-row layout: a navy top row (brand + notifications) and a white
 * bottom row (category menu) — styling lives in resources/scss/_navbar.scss,
 * scaffolded from the Rcore plugin (see that file's own header comment).
 */
use Cake\ORM\TableRegistry;

$navbarCategories = TableRegistry::getTableLocator()->get('NavbarCategories')
    ->find()
    ->contain([
        'Pages' => function ($q) {
            return $q->orderBy(['Pages.position' => 'ASC']);
        },
    ])
    ->orderBy(['NavbarCategories.position' => 'ASC'])
    ->all();

$logoPath = $this->logoPath();
$logoPath = $logoPath !== '' ? '/img/logos/' . $logoPath : '';

$contactPage = $this->contactPage();
$endItem = $contactPage !== null
    ? ['url' => '/' . $contactPage->slug, 'label' => __($contactPage->title)]
    : null;

// Navbar/notifications.php only knows how to display a notification, not
// where its image lives on disk — that's this app's ImageUploadTrait
// convention ('notifications' subdir), so the URL is built here.
$notifications = array_map(function ($notification) {
    $notification->imageUrl = $this->Url->build('/img/notifications/' . $notification->image);

    return $notification;
}, $this->activeNotifications());
?>
<nav class="site-nav">
    <div class="site-nav__top">
        <div class="site-nav__top-inner">
            <?= $this->element('Rcore.Navbar/logo', ['logoPath' => $logoPath]) ?>
            <?= $this->element('Rcore.Navbar/notifications', ['notifications' => $notifications]) ?>

            <button type="button" class="site-nav__burger" aria-label="<?= __('Menu') ?>" aria-expanded="false" aria-controls="site-nav-menu">
                <span></span>
            </button>
        </div>
    </div>

    <div class="site-nav__menubar" id="site-nav-menu">
        <div class="site-nav__menubar-inner">
            <?= $this->element('Rcore.Navbar/categoryMenu', ['categories' => $navbarCategories, 'endItem' => $endItem]) ?>
        </div>
    </div>
</nav>
<?= $this->Html->script('Rcore.navbar') ?>
