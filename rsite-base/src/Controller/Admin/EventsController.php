<?php
declare(strict_types=1);

namespace App\Controller\Admin;

class EventsController extends AppController
{
    use ImageUploadTrait;

    public function index(): void
    {
        $events = $this->fetchTable('Events')
            ->find()
            ->contain(['Categories'])
            ->orderBy(['Events.date' => 'DESC'])
            ->all();

        $this->set(compact('events'));
    }

    public function add()
    {
        $Events = $this->fetchTable('Events');
        $event = $Events->newEmptyEntity();

        if ($this->request->is('post')) {
            $data = $this->request->getData();
            /** @var \Psr\Http\Message\UploadedFileInterface|null $upload */
            $upload = $data['image'] ?? null;
            unset($data['image']);

            $hasFile = $upload !== null && $upload->getError() !== UPLOAD_ERR_NO_FILE;
            $uploadError = $hasFile ? $this->imageUploadError($upload, false) : null;

            $event = $Events->patchEntity($event, $data);

            if (!$event->getErrors() && $uploadError === null) {
                if ($hasFile) {
                    $event->image = $this->storeImageUpload($upload, 'events');
                }

                if ($Events->save($event)) {
                    $this->Flash->success(__('Event saved.'));

                    return $this->redirect(['action' => 'index']);
                }
            }

            if ($uploadError !== null) {
                $this->Flash->error($uploadError);
            }
            $this->Flash->error(__('Could not save the event, check the errors below.'));
        }

        $this->set('event', $event);
        $this->set('categories', $this->categoryOptions());

        return null;
    }

    public function edit(?string $id = null)
    {
        $Events = $this->fetchTable('Events');
        $event = $Events->get($id);

        if ($this->request->is(['post', 'put'])) {
            $data = $this->request->getData();
            /** @var \Psr\Http\Message\UploadedFileInterface|null $upload */
            $upload = $data['image'] ?? null;
            unset($data['image']);

            $hasNewFile = $upload !== null && $upload->getError() !== UPLOAD_ERR_NO_FILE;
            $uploadError = $hasNewFile ? $this->imageUploadError($upload, false) : null;

            $oldImage = $event->image;
            $event = $Events->patchEntity($event, $data);

            if (!$event->getErrors() && $uploadError === null) {
                if ($hasNewFile) {
                    $event->image = $this->storeImageUpload($upload, 'events');
                }

                if ($Events->save($event)) {
                    if ($hasNewFile) {
                        $this->deleteImageUpload('events', $oldImage);
                    }

                    $this->Flash->success(__('Event saved.'));

                    return $this->redirect(['action' => 'index']);
                }
            }

            if ($uploadError !== null) {
                $this->Flash->error($uploadError);
            }
            $this->Flash->error(__('Could not save the event, check the errors below.'));
        }

        $this->set('event', $event);
        $this->set('categories', $this->categoryOptions());

        return null;
    }

    public function delete(?string $id = null)
    {
        $this->request->allowMethod(['post', 'delete']);

        $Events = $this->fetchTable('Events');
        $event = $Events->get($id);

        if ($Events->delete($event)) {
            $this->deleteImageUpload('events', $event->image);
            $this->Flash->success(__('Event deleted.'));
        } else {
            $this->Flash->error(__('Could not delete the event.'));
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
