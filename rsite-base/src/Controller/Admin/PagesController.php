<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use App\Model\Entity\Page;

class PagesController extends AppController
{
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
}