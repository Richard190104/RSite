<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use App\Model\Entity\Page;

class PagesController extends AppController
{
    private const HOME_MAX_QUICK_ACCESS = 5;

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
}