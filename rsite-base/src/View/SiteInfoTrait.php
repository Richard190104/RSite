<?php
declare(strict_types=1);

namespace App\View;

use App\Model\Entity\News;
use App\Model\Entity\Page;
use Cake\ORM\TableRegistry;

/**
 * Shared site-wide lookups (organisation name, logo, contact page...) for
 * elements that need them — navbar, banner, footer. Mixed into AppView so
 * any template/element can call $this->organisationName() etc. directly.
 *
 * Deliberately NOT eager-loaded in a controller's initialize() — a method
 * here only runs its query when an element actually calls it, and AppView
 * is shared by the admin layout too, so an admin page that never calls
 * these never pays for them. Memoized per-request either way, so a page
 * with both navbar and footer calling organisationName() only queries once.
 */
trait SiteInfoTrait
{
    private ?string $organisationName = null;
    private ?string $city = null;
    private ?string $logoPath = null;
    private ?string $description = null;
    private bool $contactPageLoaded = false;
    private ?Page $contactPage = null;
    private ?array $quickAccessPageIds = null;
    private ?array $news = null;
    private ?string $organisationAddress = null;
    private ?string $organisationEmail = null;
    private ?string $organisationIco = null;

    public function organisationName(): string
    {
        return $this->organisationName ??= TableRegistry::getTableLocator()->get('Texts')->value('Organisation Name');
    }

    public function city(): string
    {
        return $this->city ??= TableRegistry::getTableLocator()->get('Texts')->value('City');
    }

    public function logoPath(): string
    {
        return $this->logoPath ??= TableRegistry::getTableLocator()->get('Logos')->path('Main logo');
    }

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

    public function description(): string
    {
        return $this->description ??= TableRegistry::getTableLocator()->get('Texts')->value('Footer Description');
    }

    public function organisationAddress(): string
    {
        return $this->organisationAddress ??= TableRegistry::getTableLocator()->get('Texts')->value('Organisation Address');
    }

    public function organisationEmail(): string
    {
        return $this->organisationEmail ??= TableRegistry::getTableLocator()->get('Texts')->value('Organisation Gmail');
    }

    public function organisationIco(): string
    {
        return $this->organisationIco ??= TableRegistry::getTableLocator()->get('Texts')->value('Organisation ICO');
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
