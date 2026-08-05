<?php
declare(strict_types=1);

namespace App\Controller\Admin;

class EventsController extends AppController
{
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
            $event = $Events->patchEntity($event, $this->request->getData());

            if ($Events->save($event)) {
                $this->Flash->success(__('Event saved.'));

                return $this->redirect(['action' => 'index']);
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
            $event = $Events->patchEntity($event, $this->request->getData());

            if ($Events->save($event)) {
                $this->Flash->success(__('Event saved.'));

                return $this->redirect(['action' => 'index']);
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