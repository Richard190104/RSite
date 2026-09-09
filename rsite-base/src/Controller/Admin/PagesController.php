<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use App\Model\Entity\Page;
use App\Model\Table\FeesTable;

class PagesController extends AppController
{
    use HtmlSanitizeTrait;

    private const HOME_MAX_QUICK_ACCESS = 6;
    private const ONAS_MAX_FEATURED_ACTIVITIES = 5;

    public function index(): void
    {
        $pages = $this->fetchTable('Pages')->find()->orderBy(['title' => 'ASC'])->all();
        $this->set(compact('pages'));
    }

    public function edit(?string $slug = null)
    {
        $Pages = $this->fetchTable('Pages');
        $page = $Pages->find()->where(['slug' => $slug])->firstOrFail();

        if ($page->slug === 'home') {
            return $this->editHome($Pages, $page);
        }

        if ($page->slug === 'o-nas') {
            return $this->editOnas($Pages, $page);
        }

        if ($page->slug === 'zarybnenie') {
            return $this->editZarybnenie($Pages, $page);
        }

        if ($page->slug === 'poplatky') {
            return $this->editPoplatky($page);
        }

        if ($this->request->is(['post', 'put'])) {
            $data = (array)$this->request->getData('content');
            $description = trim((string)($data['description'] ?? ''));

            $content = (array)$page->content;

            if ($description === '') {
                unset($content['description']);
            } else {
                $content['description'] = $description;
            }

            $page->content = $content;

            if ($Pages->save($page)) {
                $this->Flash->success(__('Page saved.'));

                return $this->redirect(['action' => 'index']);
            }

            $this->Flash->error(__('Could not save the page.'));
        }

        $this->set(compact('page'));

        return null;
    }

    /**
     * Homepage-specific content: an "about us" text and up to 5 other pages
     * picked for the quick access section. The feature tiles ("mini
     * banners") aren't edited here — they're regular Banners with the
     * reserved 'home_mini' location.
     */
    private function editHome($Pages, Page $page)
    {
        $otherPages = $Pages->find()
            ->where(['slug !=' => 'home'])
            ->orderBy(['title' => 'ASC'])
            ->all()
            ->combine('id', 'title')
            ->toArray();

        if ($this->request->is(['post', 'put'])) {
            $data = (array)$this->request->getData('content');
            $quickAccess = array_values(array_unique(array_map('intval', (array)($data['quick_access'] ?? []))));

            if (count($quickAccess) > self::HOME_MAX_QUICK_ACCESS) {
                $this->Flash->error(__('Please select at most {0} pages for quick access.', self::HOME_MAX_QUICK_ACCESS));
            } else {
                $page->content = [
                    'about_us_text' => (string)($data['about_us_text'] ?? ''),
                    'quick_access' => $quickAccess,
                ] + (array)$page->content;

                if ($Pages->save($page)) {
                    $this->Flash->success(__('Homepage saved.'));

                    return $this->redirect(['action' => 'index']);
                }

                $this->Flash->error(__('Could not save the homepage.'));
            }
        }

        $this->set('page', $page);
        $this->set('otherPages', $otherPages);
        $this->render('edit_home');

        return null;
    }

    /**
     * "O nás" page-specific content: the "about us" text shown on the page
     * itself (own field, same pattern as home's about_us_text) next to the
     * page's 'onas-main' banner image — that image is a regular Banner
     * under its own virtual location, managed in Banners like the
     * homepage/fishing grounds tiles, not edited here.
     *
     * Also keeps the same 'description' field every other fixed page has
     * (see edit()) — the teaser text shown on this page's homepage
     * "quick access" card, if it's ever added there. Losing this field
     * here would silently blank that teaser on save.
     *
     * Also picks up to 5 Events shown as the public page's "Naše aktivity"
     * timeline. Unlike quick_access (a handful of Pages, fine as a plain
     * checkbox list), Events can number in the hundreds, so the picker
     * option list is passed to the template as JSON for a type-to-filter
     * JS widget (see templates/Admin/Pages/edit_onas.php) rather than
     * rendered as one giant checkbox per row.
     */
    private function editOnas($Pages, Page $page)
    {
        if ($this->request->is(['post', 'put'])) {
            $data = (array)$this->request->getData('content');
            $description = trim((string)($data['description'] ?? ''));
            $featuredActivities = array_values(array_unique(array_map(
                'intval',
                (array)($data['featured_activities'] ?? []),
            )));

            if (count($featuredActivities) > self::ONAS_MAX_FEATURED_ACTIVITIES) {
                $this->Flash->error(__(
                    'Please select at most {0} activities for the timeline.',
                    self::ONAS_MAX_FEATURED_ACTIVITIES,
                ));

                $this->set('page', $page);
                $this->set('events', $this->onasActivityOptions());
                $this->render('edit_onas');

                return null;
            }

            $content = (array)$page->content;
            $content['about_us_text'] = (string)($data['about_us_text'] ?? '');
            $content['featured_activities'] = $featuredActivities;

            if ($description === '') {
                unset($content['description']);
            } else {
                $content['description'] = $description;
            }

            $page->content = $content;

            if ($Pages->save($page)) {
                $this->Flash->success(__('Page saved.'));

                return $this->redirect(['action' => 'index']);
            }

            $this->Flash->error(__('Could not save the page.'));
        }

        $this->set('page', $page);
        $this->set('events', $this->onasActivityOptions());
        $this->render('edit_onas');

        return null;
    }

    /**
     * Every Event, newest first, as plain arrays (id/title/date) for the
     * "Naše aktivity" picker's JS — a single upfront fetch the widget
     * filters client-side, rather than a checkbox per row (see editOnas()).
     *
     * @return array<int, array{id: int, title: string, date: string}>
     */
    private function onasActivityOptions(): array
    {
        return $this->fetchTable('Events')
            ->find()
            ->select(['id', 'title', 'date'])
            ->orderBy(['date' => 'DESC'])
            ->all()
            ->map(fn ($event) => [
                'id' => $event->id,
                'title' => $event->title,
                'date' => $event->date?->i18nFormat('d. MMMM yyyy') ?? '',
            ])
            ->toList();
    }

    /**
     * "Zarybnenie a úlovky" page: the same 'description' field every other
     * fixed page has (see edit()) — the teaser text shown on this page's
     * homepage "quick access" card — plus a list of the StockingDocument/
     * CatchDocument rows (each nothing but a title and a PDF) managed by
     * their own controllers (Admin\StockingDocumentsController,
     * Admin\CatchDocumentsController), with links into those controllers'
     * add/edit/delete actions. Those controllers stay separate (full CRUD,
     * file uploads) — they're just not their own sidebar entry, since from
     * an admin's point of view they're "part of" this one page.
     */
    private function editZarybnenie($Pages, Page $page)
    {
        if ($this->request->is(['post', 'put'])) {
            $data = (array)$this->request->getData('content');
            $description = trim((string)($data['description'] ?? ''));

            $content = (array)$page->content;

            if ($description === '') {
                unset($content['description']);
            } else {
                $content['description'] = $description;
            }

            $page->content = $content;

            if ($Pages->save($page)) {
                $this->Flash->success(__('Page saved.'));

                return $this->redirect(['action' => 'index']);
            }

            $this->Flash->error(__('Could not save the page.'));
        }

        $stockingDocuments = $this->fetchTable('StockingDocuments')
            ->find()
            ->orderBy(['created' => 'DESC'])
            ->all();

        $catchDocuments = $this->fetchTable('CatchDocuments')
            ->find()
            ->orderBy(['created' => 'DESC'])
            ->all();

        $this->set(compact('page', 'stockingDocuments', 'catchDocuments'));
        $this->render('edit_zarybnenie');

        return null;
    }

    /**
     * "Poplatky" page: the same 'description' field every other fixed page
     * has (see edit()) — the teaser text shown on this page's homepage
     * "quick access" card — plus a list of Fee rows (title/price/category),
     * managed by their own controller (Admin\FeesController), grouped by
     * category here the same way the public page groups them. Categories
     * are the fixed set in FeesTable::CATEGORIES, not the shared Categories
     * table. 'notice' is a WYSIWYG-edited HTML blob covering everything
     * that used to be hardcoded below the fee tables in
     * templates/Pages/poplatky.php (permit issue dates, fishing licence
     * exemptions, payment account) — sanitized the same way
     * Events/News::content is (see HtmlSanitizeTrait), since it's admin
     * input rendered raw on the public page.
     */
    private function editPoplatky(Page $page)
    {
        $Pages = $this->fetchTable('Pages');

        if ($this->request->is(['post', 'put'])) {
            $data = (array)$this->request->getData('content');
            $description = trim((string)($data['description'] ?? ''));
            $notice = trim((string)($data['notice'] ?? ''));
            if ($notice !== '') {
                $notice = $this->sanitizeHtml($notice);
            }

            $content = (array)$page->content;

            if ($description === '') {
                unset($content['description']);
            } else {
                $content['description'] = $description;
            }

            if ($notice === '') {
                unset($content['notice']);
            } else {
                $content['notice'] = $notice;
            }

            $page->content = $content;

            if ($Pages->save($page)) {
                $this->Flash->success(__('Page saved.'));

                return $this->redirect(['prefix' => 'Admin', 'controller' => 'Pages', 'action' => 'edit', 'poplatky']);
            }

            $this->Flash->error(__('Could not save the page.'));
        }

        $fees = $this->fetchTable('Fees')
            ->find()
            ->orderBy(['position' => 'ASC', 'title' => 'ASC'])
            ->all();

        $feesByCategory = [];
        foreach ($fees as $fee) {
            $feesByCategory[$fee->category][] = $fee;
        }

        // Ordered by the fixed FeesTable::CATEGORIES list, not alphabetically
        // or by first-seen — same order the public page groups sections in.
        $feesByCategory = array_replace(
            array_fill_keys(array_keys(FeesTable::CATEGORIES), []),
            $feesByCategory,
        );

        $this->set('page', $page);
        $this->set(compact('feesByCategory'));
        $this->render('edit_poplatky');

        return null;
    }
}