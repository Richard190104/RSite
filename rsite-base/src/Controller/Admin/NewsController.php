<?php
declare(strict_types=1);

namespace App\Controller\Admin;

class NewsController extends AppController
{
    use ImageUploadTrait;
    use HtmlSanitizeTrait;

    public function index(): void
    {
        $news = $this->fetchTable('News')
            ->find()
            ->contain(['Categories'])
            ->orderBy(['date' => 'DESC'])
            ->all();

        $this->set(compact('news'));
    }

    public function add()
    {
        $News = $this->fetchTable('News');
        $article = $News->newEmptyEntity();

        if ($this->request->is('post')) {
            $data = $this->request->getData();
            /** @var \Psr\Http\Message\UploadedFileInterface|null $upload */
            $upload = $data['image'] ?? null;
            unset($data['image']);

            if (!empty($data['content'])) {
                $data['content'] = $this->sanitizeHtml($data['content']);
            }

            $article = $News->patchEntity($article, $data);
            $uploadError = $this->imageUploadError($upload, true);

            if (!$article->getErrors() && $uploadError === null) {
                $article->image = $this->storeImageUpload($upload, 'news');

                if ($News->save($article)) {
                    $this->Flash->success(__('News article saved.'));

                    return $this->redirect(['action' => 'index']);
                }
            }

            if ($uploadError !== null) {
                $this->Flash->error($uploadError);
            }
            $this->Flash->error(__('Could not save the article, check the errors below.'));
        }

        $this->set('article', $article);
        $this->set('categories', $this->categoryOptions());

        return null;
    }

    public function edit(?string $id = null)
    {
        $News = $this->fetchTable('News');
        $article = $News->get($id);

        if ($this->request->is(['post', 'put'])) {
            $data = $this->request->getData();
            /** @var \Psr\Http\Message\UploadedFileInterface|null $upload */
            $upload = $data['image'] ?? null;
            unset($data['image']);

            $hasNewFile = $upload !== null && $upload->getError() !== UPLOAD_ERR_NO_FILE;
            $uploadError = $hasNewFile ? $this->imageUploadError($upload, false) : null;

            if (!empty($data['content'])) {
                $data['content'] = $this->sanitizeHtml($data['content']);
            }

            $oldImage = $article->image;
            $article = $News->patchEntity($article, $data);

            if (!$article->getErrors() && $uploadError === null) {
                if ($hasNewFile) {
                    $article->image = $this->storeImageUpload($upload, 'news');
                }

                if ($News->save($article)) {
                    if ($hasNewFile) {
                        $this->deleteImageUpload('news', $oldImage);
                    }

                    $this->Flash->success(__('News article saved.'));

                    return $this->redirect(['action' => 'index']);
                }
            }

            if ($uploadError !== null) {
                $this->Flash->error($uploadError);
            }
            $this->Flash->error(__('Could not save the article, check the errors below.'));
        }

        $this->set('article', $article);
        $this->set('categories', $this->categoryOptions());

        return null;
    }

    public function delete(?string $id = null)
    {
        $this->request->allowMethod(['post', 'delete']);

        $News = $this->fetchTable('News');
        $article = $News->get($id);

        if ($News->delete($article)) {
            $this->deleteImageUpload('news', $article->image);

            $this->Flash->success(__('News article deleted.'));
        } else {
            $this->Flash->error(__('Could not delete the article.'));
        }

        return $this->redirect(['action' => 'index']);
    }

    /**
     * @return array<int, string>
     */
    private function categoryOptions(): array
    {
        return $this->fetchTable('Categories')
            ->find()
            ->orderBy(['title' => 'ASC'])
            ->all()
            ->combine('id', 'title')
            ->toArray();
    }
}