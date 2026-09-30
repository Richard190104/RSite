<?php
declare(strict_types=1);

namespace App\View;

use App\Model\Entity\News;
use Rcore\Model\Entity\Page;
use Cake\ORM\TableRegistry;

/**
 * Domain-specific site-wide lookups (contact page, news, the reviry map
 * subtitle, placeholder images, homepage quick-access) for elements that
 * need them — navbar, banner, footer. Mixed into AppView alongside
 * Rcore\View\SiteInfoTrait, which covers the generic organisation-name/
 * contact/social/palette/logo/notifications accessors this app used to
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
}
