<?php
declare(strict_types=1);

namespace App\View;

use App\Model\Entity\News;
use App\Model\Entity\Notification;
use App\Model\Entity\Page;
use Cake\I18n\Date;
use Cake\ORM\TableRegistry;

/**
 * Domain-specific site-wide lookups (contact page, news, notifications,
 * the reviry map subtitle, placeholder images, homepage quick-access) for
 * elements that need them — navbar, banner, footer. Mixed into AppView
 * alongside Rcore\View\SiteInfoTrait, which covers the generic
 * organisation-name/contact/social/palette/logo accessors this app used to
 * also define here before those tables' code moved into the shared Rcore
 * plugin.
 *
 * Deliberately NOT eager-loaded in a controller's initialize() — a method
 * here only runs its query when an element actually calls it, and AppView
 * is shared by the admin layout too, so an admin page that never calls
 * these never pays for them. Memoized per-request either way, so a page
 * with both navbar and footer calling the same accessor only queries once.
 */
trait SiteInfoTrait
{
    private bool $contactPageLoaded = false;
    private ?Page $contactPage = null;
    private ?string $reviryMapSubtitle = null;
    private ?array $quickAccessPageIds = null;
    private ?array $news = null;
    private ?array $activeNotifications = null;
    private bool $popupNotificationLoaded = false;
    private ?Notification $popupNotification = null;

    public function contactPage(): ?Page
    {
        if (!$this->contactPageLoaded) {
            $this->contactPage = TableRegistry::getTableLocator()->get('Pages')
                ->find()
                ->select(['title', 'slug'])
                ->where(['slug' => 'kontakt'])
                ->first();
            $this->contactPageLoaded = true;
        }

        return $this->contactPage;
    }

    public function reviryMapSubtitle(): string
    {
        return $this->reviryMapSubtitle ??= TableRegistry::getTableLocator()->get('Rcore.Texts')->value('Reviry map subtitle');
    }

    /**
     * A stock nature photo's public URL (webroot/img/placeholders/), for
     * News/Event/FishingGround cards that have no image of their own — or,
     * if the "Automatic Images" setting is off, a neutral "no image"
     * graphic instead of a random photo (same filename every time in that
     * case; see PlaceholderImagesTable::random()). Deliberately not
     * memoized like this trait's other getters: a listing page calls this
     * once per card, and each one should get its own independent pick
     * rather than the same photo repeated down the whole page.
     */
    public function randomPlaceholderImage(): string
    {
        return '/img/placeholders/' . TableRegistry::getTableLocator()->get('PlaceholderImages')->random();
    }

    /**
     * The homepage's quick-access page ids (Page::$content['quick_access'],
     * set via Admin\PagesController::editHome()) — the same list the
     * quickAccess element renders on the homepage itself. Elements like
     * footer.php that aren't rendering the home page's own $page entity
     * still want the same list, so this is the one place that reads it.
     *
     * @return array<int, int>
     */
    public function quickAccessPageIds(): array
    {
        if ($this->quickAccessPageIds !== null) {
            return $this->quickAccessPageIds;
        }

        $home = TableRegistry::getTableLocator()->get('Pages')
            ->find()
            ->select(['content'])
            ->where(['slug' => 'home'])
            ->first();

        return $this->quickAccessPageIds = (array)($home?->content['quick_access'] ?? []);
    }

    /**
     * Newest news articles for the homepage's "Aktuálne novinky" section.
     *
     * @return array<int, \App\Model\Entity\News>
     */
    public function getNews(int $limit = 10): array
    {
        if ($this->news !== null) {
            return $this->news;
        }

        return $this->news = TableRegistry::getTableLocator()->get('News')
            ->find()
            ->contain(['Categories'])
            ->orderBy(['date' => 'DESC'])
            ->limit($limit)
            ->all()
            ->toList();
    }

    /**
     * Notifications currently shown in the navbar's bell dropdown: only
     * those marked active (settings.is_active — a free-form JSON flag, not
     * its own column, see NotificationsTable) whose valid_from/valid_to
     * window includes today. is_active can't be filtered in SQL since it
     * lives inside the JSON settings column, so it's checked in PHP after
     * the date range has already narrowed the query.
     *
     * @return array<int, \App\Model\Entity\Notification>
     */
    public function activeNotifications(): array
    {
        if ($this->activeNotifications !== null) {
            return $this->activeNotifications;
        }

        $today = Date::now();

        $notifications = TableRegistry::getTableLocator()->get('Notifications')
            ->find()
            ->where(['valid_from <=' => $today, 'valid_to >=' => $today])
            ->orderBy(['valid_from' => 'DESC'])
            ->all()
            ->filter(fn ($notification) => (bool)($notification->settings['is_active'] ?? true))
            ->toList();

        return $this->activeNotifications = $notifications;
    }

    /**
     * One random active notification flagged settings.show_as_popup, shown
     * as a small popup on page load — a different one may be picked on
     * each request/page since the choice isn't sticky across requests.
     * Null when no active notification has the flag set.
     */
    public function popupNotification(): ?Notification
    {
        if ($this->popupNotificationLoaded) {
            return $this->popupNotification;
        }
        $this->popupNotificationLoaded = true;

        $candidates = array_values(array_filter(
            $this->activeNotifications(),
            fn ($notification) => (bool)($notification->settings['show_as_popup'] ?? false),
        ));

        if (!$candidates) {
            return $this->popupNotification = null;
        }

        return $this->popupNotification = $candidates[array_rand($candidates)];
    }
}
