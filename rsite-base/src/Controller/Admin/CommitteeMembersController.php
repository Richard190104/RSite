<?php
declare(strict_types=1);

namespace App\Controller\Admin;

class CommitteeMembersController extends AppController
{
    use ImageUploadTrait;

    public function index(): void
    {
        $committeeMembers = $this->fetchTable('CommitteeMembers')
            ->find()
            ->orderBy(['section' => 'ASC', 'name' => 'ASC'])
            ->all();

        $this->set(compact('committeeMembers'));
    }

    public function add()
    {
        $CommitteeMembers = $this->fetchTable('CommitteeMembers');
        $committeeMember = $CommitteeMembers->newEmptyEntity();

        if ($this->request->is('post')) {
            $data = $this->request->getData();
            /** @var \Psr\Http\Message\UploadedFileInterface|null $upload */
            $upload = $data['photo'] ?? null;
            unset($data['photo']);

            $committeeMember = $CommitteeMembers->patchEntity($committeeMember, $data);
            $hasFile = $upload !== null && $upload->getError() !== UPLOAD_ERR_NO_FILE;
            $uploadError = $hasFile ? $this->imageUploadError($upload, false) : null;

            if (!$committeeMember->getErrors() && $uploadError === null) {
                if ($hasFile) {
                    $committeeMember->photo = $this->storeImageUpload($upload, 'committee');
                }

                if ($CommitteeMembers->save($committeeMember)) {
                    $this->Flash->success(__('Committee member saved.'));

                    return $this->redirect(['action' => 'index']);
                }
            }

            if ($uploadError !== null) {
                $this->Flash->error($uploadError);
            }
            $this->Flash->error(__('Could not save the committee member, check the errors below.'));
        }

        $this->set('committeeMember', $committeeMember);

        return null;
    }

    public function edit(?string $id = null)
    {
        $CommitteeMembers = $this->fetchTable('CommitteeMembers');
        $committeeMember = $CommitteeMembers->get($id);

        if ($this->request->is(['post', 'put'])) {
            $data = $this->request->getData();
            /** @var \Psr\Http\Message\UploadedFileInterface|null $upload */
            $upload = $data['photo'] ?? null;
            unset($data['photo']);

            $hasNewFile = $upload !== null && $upload->getError() !== UPLOAD_ERR_NO_FILE;
            $uploadError = $hasNewFile ? $this->imageUploadError($upload, false) : null;

            $oldPhoto = $committeeMember->photo;
            $committeeMember = $CommitteeMembers->patchEntity($committeeMember, $data);

            if (!$committeeMember->getErrors() && $uploadError === null) {
                if ($hasNewFile) {
                    $committeeMember->photo = $this->storeImageUpload($upload, 'committee');
                }

                if ($CommitteeMembers->save($committeeMember)) {
                    if ($hasNewFile && $oldPhoto) {
                        $this->deleteImageUpload('committee', $oldPhoto);
                    }

                    $this->Flash->success(__('Committee member saved.'));

                    return $this->redirect(['action' => 'index']);
                }
            }

            if ($uploadError !== null) {
                $this->Flash->error($uploadError);
            }
            $this->Flash->error(__('Could not save the committee member, check the errors below.'));
        }

        $this->set('committeeMember', $committeeMember);

        return null;
    }

    public function delete(?string $id = null)
    {
        $this->request->allowMethod(['post', 'delete']);

        $CommitteeMembers = $this->fetchTable('CommitteeMembers');
        $committeeMember = $CommitteeMembers->get($id);

        if ($CommitteeMembers->delete($committeeMember)) {
            if ($committeeMember->photo) {
                $this->deleteImageUpload('committee', $committeeMember->photo);
            }

            $this->Flash->success(__('Committee member deleted.'));
        } else {
            $this->Flash->error(__('Could not delete the committee member.'));
        }

        return $this->redirect(['action' => 'index']);
    }
}
