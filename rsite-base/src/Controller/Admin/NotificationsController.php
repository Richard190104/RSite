<?php
declare(strict_types=1);

namespace App\Controller\Admin;

class NotificationsController extends AppController
{
    use ImageUploadTrait;

    public function index(): void
    {
        $notifications = $this->fetchTable('Notifications')
            ->find()
            ->orderBy(['valid_from' => 'DESC'])
            ->all();

        $this->set(compact('notifications'));
    }

    public function add()
    {
        $Notifications = $this->fetchTable('Notifications');
        $notification = $Notifications->newEmptyEntity();

        if ($this->request->is('post')) {
            $data = $this->request->getData();
            /** @var \Psr\Http\Message\UploadedFileInterface|null $upload */
            $upload = $data['image'] ?? null;
            unset($data['image']);

            $notification = $Notifications->patchEntity($notification, $data);
            $uploadError = $this->imageUploadError($upload, true);

            if (!$notification->getErrors() && $uploadError === null) {
                $notification->image = $this->storeImageUpload($upload, 'notifications');

                if ($Notifications->save($notification)) {
                    $this->Flash->success(__('Notification saved.'));

                    return $this->redirect(['action' => 'index']);
                }
            }

            if ($uploadError !== null) {
                $this->Flash->error($uploadError);
            }
            $this->Flash->error(__('Could not save the notification, check the errors below.'));
        }

        $this->set('notification', $notification);

        return null;
    }

    public function edit(?string $id = null)
    {
        $Notifications = $this->fetchTable('Notifications');
        $notification = $Notifications->get($id);

        if ($this->request->is(['post', 'put'])) {
            $data = $this->request->getData();
            /** @var \Psr\Http\Message\UploadedFileInterface|null $upload */
            $upload = $data['image'] ?? null;
            unset($data['image']);

            $hasNewFile = $upload !== null && $upload->getError() !== UPLOAD_ERR_NO_FILE;
            $uploadError = $hasNewFile ? $this->imageUploadError($upload, false) : null;

            $oldImage = $notification->image;
            $notification = $Notifications->patchEntity($notification, $data);

            if (!$notification->getErrors() && $uploadError === null) {
                if ($hasNewFile) {
                    $notification->image = $this->storeImageUpload($upload, 'notifications');
                }

                if ($Notifications->save($notification)) {
                    if ($hasNewFile) {
                        $this->deleteImageUpload('notifications', $oldImage);
                    }

                    $this->Flash->success(__('Notification saved.'));

                    return $this->redirect(['action' => 'index']);
                }
            }

            if ($uploadError !== null) {
                $this->Flash->error($uploadError);
            }
            $this->Flash->error(__('Could not save the notification, check the errors below.'));
        }

        $this->set('notification', $notification);

        return null;
    }

    public function toggleActive(?string $id = null)
    {
        $this->request->allowMethod(['post']);

        $Notifications = $this->fetchTable('Notifications');
        $notification = $Notifications->get($id);

        $settings = $notification->settings ?? [];
        $settings['is_active'] = !(bool)($settings['is_active'] ?? true);
        $notification->settings = $settings;

        if (!$Notifications->save($notification)) {
            $this->Flash->error(__('Could not update the notification.'));
        }

        return $this->redirect(['action' => 'index']);
    }

    public function delete(?string $id = null)
    {
        $this->request->allowMethod(['post', 'delete']);

        $Notifications = $this->fetchTable('Notifications');
        $notification = $Notifications->get($id);

        if ($Notifications->delete($notification)) {
            $this->deleteImageUpload('notifications', $notification->image);

            $this->Flash->success(__('Notification deleted.'));
        } else {
            $this->Flash->error(__('Could not delete the notification.'));
        }

        return $this->redirect(['action' => 'index']);
    }
}
