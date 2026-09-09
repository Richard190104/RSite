<?php
declare(strict_types=1);

/**
 * CakePHP(tm) : Rapid Development Framework (https://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 * @link      https://cakephp.org CakePHP(tm) Project
 * @since     0.2.9
 * @license   https://opensource.org/licenses/mit-license.php MIT License
 */
namespace App\Controller;

use Cake\Core\Configure;
use Cake\Http\Exception\ForbiddenException;
use Cake\Http\Exception\NotFoundException;
use Cake\Http\Response;
use Cake\I18n\Date;
use Cake\Routing\Router;
use Cake\View\Exception\MissingTemplateException;

/**
 * Static content controller
 *
 * This controller will render views from templates/Pages/
 *
 * @link https://book.cakephp.org/4/en/controllers/pages-controller.html
 */
class PagesController extends AppController
{
    /**
     * The homepage. For now just fetches the fixed "home" page row (title +
     * content: about_us_text / quick_access) — see Admin\PagesController::
     * editHome() for how the content is edited. Rendering comes later.
     */
    public function home(): void
    {
        $page = $this->fetchTable('Pages')->find()->where(['slug' => 'home'])->firstOrFail();

        $this->set(compact('page'));
    }

    /**
     * Public contact page. Contact fields come from Texts (admin); the page
     * row itself is the fixed "kontakt" slug used by the navbar button.
     */
    public function kontakt(): void
    {
        $page = $this->fetchTable('Pages')->find()->where(['slug' => 'kontakt'])->firstOrFail();

        $this->set(compact('page'));
    }

    /**
     * Public "about us" page. Fixed "o-nas" slug — text comes from
     * page.content['about_us_text'] (Admin\PagesController::editOnas()),
     * the feature image is a regular Banner under the reserved 'onas-main'
     * virtual location (same pattern as the homepage/fishing-grounds
     * images, see BannersTable::VIRTUAL_LOCATIONS). Also lists the
     * organisation's committee (Admin\CommitteeMembersController).
     */
    public function onas(): void
    {
        $page = $this->fetchTable('Pages')->find()->where(['slug' => 'o-nas'])->firstOrFail();

        $mainBanner = $this->fetchTable('Banners')
            ->find()
            ->where(['location' => 'onas-main', 'is_enabled' => true])
            ->orderBy(['id' => 'ASC'])
            ->first();

        $committeeMembers = $this->fetchTable('CommitteeMembers')
            ->find()
            ->orderBy(['section' => 'ASC', 'name' => 'ASC'])
            ->all();

        // Up to 5 Events an admin picked in Admin\PagesController::editOnas()
        // for this page's "Naše aktivity" timeline (templates/Pages/onas.php)
        // — always shown oldest-first regardless of the order they were
        // picked in, since the timeline reads left-to-right as a history.
        // Categories is contained so the template can link a timeline item
        // to that category's public gallery page when one exists (only
        // when Category::show_in_gallery — see templates/Pages/onas.php).
        $featuredActivityIds = array_map('intval', (array)($page->content['featured_activities'] ?? []));
        $featuredActivities = $featuredActivityIds
            ? $this->fetchTable('Events')
                ->find()
                ->contain(['Categories'])
                ->where(['Events.id IN' => $featuredActivityIds])
                ->orderBy(['Events.date' => 'ASC'])
                ->all()
                ->toList()
            : [];

        $this->set(compact('page', 'mainBanner', 'committeeMembers', 'featuredActivities'));
    }

    /**
     * Public activities page: upcoming event cards, activity categories, and
     * a front-end calendar fed by all Events from admin.
     */
    public function aktivity(): void
    {
        $page = $this->fetchTable('Pages')->find()->where(['slug' => 'aktivity'])->firstOrFail();
        $today = Date::now();

        $Events = $this->fetchTable('Events');

        $upcomingEvents = $Events->find()
            ->contain(['Categories'])
            ->where(['Events.date >=' => $today])
            ->orderBy(['Events.date' => 'ASC'])
            ->limit(3)
            ->all()
            ->toList();

        $allEvents = $Events->find()
            ->contain(['Categories'])
            ->where(['Events.date IS NOT' => null])
            ->orderBy(['Events.date' => 'ASC'])
            ->all()
            ->toList();

        $categoryIds = $Events->find()
            ->select(['category_id'])
            ->where(['category_id IS NOT' => null])
            ->distinct(['category_id'])
            ->all()
            ->extract('category_id')
            ->toList();

        // Only categories flagged Category::show_in_gallery are shown here
        // — same flag as the public gallery (see GalleryController), so a
        // tile only ever appears once there's actually a gallery page for
        // an admin to send visitors to (see the tile's link below).
        $categories = $categoryIds
            ? $this->fetchTable('Categories')
                ->find()
                ->where(['id IN' => $categoryIds, 'show_in_gallery' => true])
                ->orderBy(['title' => 'ASC'])
                ->limit(5)
                ->all()
                ->toList()
            : [];

        // Carries the same fields as each event card's data-* attributes
        // (see templates/Pages/aktivity.php) so the calendar's own
        // JS-rendered list items (aktivity-calendar.js) can open the exact
        // same popup (aktivity-event-modal.js) as the upcoming-events cards.
        // Not static: needs $this->fetchTable() for the placeholder photo
        // fallback, one freshly picked per event with no image of its own —
        // same PlaceholderImagesTable::random() the upcoming-events cards
        // use (see templates/Pages/aktivity.php).
        $placeholderImages = $this->fetchTable('PlaceholderImages');
        $calendarEvents = array_map(function ($event) use ($placeholderImages) {
            $imagePath = $event->image
                ? '/img/events/' . $event->image
                : '/img/placeholders/' . $placeholderImages->random();

            return [
                'id' => $event->id,
                'title' => $event->title,
                'description' => $event->description,
                'date' => $event->date?->format('Y-m-d'),
                'displayDate' => $event->date?->i18nFormat('d. MMMM yyyy'),
                'location' => $event->location,
                'time' => $event->time,
                'image' => Router::url($imagePath),
                'category' => $event->category ? __($event->category->title) : '',
                'content' => $event->content ?? '',
            ];
        }, $allEvents);

        $this->set(compact('page', 'upcomingEvents', 'categories', 'calendarEvents'));
    }

    public function news(): void
    {
        $page = $this->fetchTable('Pages')->find()->where(['slug' => 'news'])->firstOrFail();

        $News = $this->fetchTable('News');
        $query = $News->find()->contain(['Categories'])->orderBy(['News.date' => 'DESC']);

        $categoryId = $this->request->getQuery('category');
        if ($categoryId !== null && $categoryId !== '') {
            $query->where(['News.category_id' => (int)$categoryId]);
        }

        $news = $this->paginate($query, ['limit' => 8]);

        $categoryIds = $News->find()
            ->select(['category_id'])
            ->where(['category_id IS NOT' => null])
            ->distinct(['category_id'])
            ->all()
            ->extract('category_id')
            ->toList();

        $categories = $categoryIds
            ? $this->fetchTable('Categories')
                ->find()
                ->where(['id IN' => $categoryIds, 'parent_id IS' => null])
                ->orderBy(['title' => 'ASC'])
                ->all()
                ->toList()
            : [];

        $this->set(compact('page', 'news', 'categories', 'categoryId'));
    }

    public function reviry(): void
    {
        $page = $this->fetchTable('Pages')->find()->where(['slug' => 'reviry'])->firstOrFail();

        // ->toList(): the template iterates this twice (the stylized map,
        // then the card grid) — a bare ResultSet is a single-pass cursor
        // and would come back empty on the second pass.
        $fishingGrounds = $this->fetchTable('FishingGrounds')
            ->find()
            ->orderBy(['title' => 'ASC'])
            ->all()
            ->toList();

        $this->set(compact('page', 'fishingGrounds'));
    }

    /**
     * Public "Zarybnenie a úlovky" page: fish stocking records
     * (Admin\StockingsController) and member catch records
     * (Admin\CatchRecordsController), each newest first.
     */
    public function zarybnenie(): void
    {
        $page = $this->fetchTable('Pages')->find()->where(['slug' => 'zarybnenie'])->firstOrFail();

        $stockingDocuments = $this->fetchTable('StockingDocuments')
            ->find()
            ->orderBy(['created' => 'DESC'])
            ->all()
            ->toList();

        $catchDocuments = $this->fetchTable('CatchDocuments')
            ->find()
            ->orderBy(['created' => 'DESC'])
            ->all()
            ->toList();

        $this->set(compact('page', 'stockingDocuments', 'catchDocuments'));
    }

    /**
     * Displays a view
     *
     *
     * @param string ...$path Path segments.
     * @return \Cake\Http\Response|null
     * @throws \Cake\Http\Exception\ForbiddenException When a directory traversal attempt.
     * @throws \Cake\View\Exception\MissingTemplateException When the view file could not
     *   be found and in debug mode.
     * @throws \Cake\Http\Exception\NotFoundException When the view file could not
     *   be found and not in debug mode.
     * @throws \Cake\View\Exception\MissingTemplateException In debug mode.
     */
    public function display(string ...$path): ?Response
    {
        if (!$path) {
            return $this->redirect('/');
        }
        if (in_array('..', $path, true) || in_array('.', $path, true)) {
            throw new ForbiddenException();
        }
        $page = $subpage = null;

        if (!empty($path[0])) {
            $page = $path[0];
        }
        if (!empty($path[1])) {
            $subpage = $path[1];
        }

        $pageEntity = $this->fetchTable('Pages')->find()->where(['slug' => $page])->first();
        if ($pageEntity !== null) {
            $page = $pageEntity;
        }

        $this->set(compact('page', 'subpage'));

        try {
            return $this->render(implode('/', $path));
        } catch (MissingTemplateException $exception) {
            if (Configure::read('debug')) {
                throw $exception;
            }
            throw new NotFoundException();
        }
    }
}
