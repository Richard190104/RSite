<?php
/**
 * @var \App\View\AppView $this
 *
 * Builds each slot's content as its own variable (usually by calling one of
 * this plugin's other Navbar/* elements, but any app-specific markup works
 * just as well), then hands all three to Rcore.Navbar/layout, which owns
 * the actual two-row shell/arrangement. This app's own data here
 * (NavbarCategories/Pages, Logos, contact page, Notifications — none of
 * these tables live in Rcore) only ever feeds into building those slots.
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

$brand = $this->element('Rcore.Navbar/logo', ['logoPath' => $logoPath]);
$notificationsHtml = $this->element('Rcore.Navbar/notifications', ['notifications' => $notifications]);
$categoryMenu = $this->element('Rcore.Navbar/categoryMenu', ['categories' => $navbarCategories, 'endItem' => $endItem]);
?>
<?= $this->element('Rcore.Navbar/layout', [
    'brand' => $brand,
    'notifications' => $notificationsHtml,
    'categoryMenu' => $categoryMenu,
]) ?>
