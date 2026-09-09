<?php
declare(strict_types=1);

namespace App\Controller\Admin;

class CatchDocumentsController extends AppController
{
    use FileUploadTrait;

    public function add()
    {
        $CatchDocuments = $this->fetchTable('CatchDocuments');
        $catchDocument = $CatchDocuments->newEmptyEntity();

        if ($this->request->is('post')) {
            $data = $this->request->getData();
            /** @var \Psr\Http\Message\UploadedFileInterface|null $upload */
            $upload = $data['file'] ?? null;
            unset($data['file']);

            $hasFile = $upload !== null && $upload->getError() !== UPLOAD_ERR_NO_FILE;
            $uploadError = $this->fileUploadError($upload, true);

            $catchDocument = $CatchDocuments->patchEntity($catchDocument, $data);

            if (!$catchDocument->getErrors() && $uploadError === null) {
                $catchDocument->file = $this->storeFileUpload($upload, 'catch-documents');

                if ($CatchDocuments->save($catchDocument)) {
                    $this->Flash->success(__('Document saved.'));

                    return $this->redirect(['prefix' => 'Admin', 'controller' => 'Pages', 'action' => 'edit', 'zarybnenie']);
                }
            }

            if ($uploadError !== null) {
                $this->Flash->error($uploadError);
            }
            $this->Flash->error(__('Could not save the document, check the errors below.'));
        }

        $this->set('catchDocument', $catchDocument);

        return null;
    }

    public function edit(?string $id = null)
    {
        $CatchDocuments = $this->fetchTable('CatchDocuments');
        $catchDocument = $CatchDocuments->get($id);

        if ($this->request->is(['post', 'put'])) {
            $data = $this->request->getData();
            /** @var \Psr\Http\Message\UploadedFileInterface|null $upload */
            $upload = $data['file'] ?? null;
            unset($data['file']);

            $hasNewFile = $upload !== null && $upload->getError() !== UPLOAD_ERR_NO_FILE;
            $uploadError = $hasNewFile ? $this->fileUploadError($upload, false) : null;

            $oldFile = $catchDocument->file;
            $catchDocument = $CatchDocuments->patchEntity($catchDocument, $data);

            if (!$catchDocument->getErrors() && $uploadError === null) {
                if ($hasNewFile) {
                    $catchDocument->file = $this->storeFileUpload($upload, 'catch-documents');
                }

                if ($CatchDocuments->save($catchDocument)) {
                    if ($hasNewFile && $oldFile) {
                        $this->deleteFileUpload('catch-documents', $oldFile);
                    }

                    $this->Flash->success(__('Document saved.'));

                    return $this->redirect(['prefix' => 'Admin', 'controller' => 'Pages', 'action' => 'edit', 'zarybnenie']);
                }
            }

            if ($uploadError !== null) {
                $this->Flash->error($uploadError);
            }
            $this->Flash->error(__('Could not save the document, check the errors below.'));
        }

        $this->set('catchDocument', $catchDocument);

        return null;
    }

    public function delete(?string $id = null)
    {
        $this->request->allowMethod(['post', 'delete']);

        $CatchDocuments = $this->fetchTable('CatchDocuments');
        $catchDocument = $CatchDocuments->get($id);

        if ($CatchDocuments->delete($catchDocument)) {
            $this->deleteFileUpload('catch-documents', $catchDocument->file);
            $this->Flash->success(__('Document deleted.'));
        } else {
            $this->Flash->error(__('Could not delete the document.'));
        }

        return $this->redirect(['prefix' => 'Admin', 'controller' => 'Pages', 'action' => 'edit', 'zarybnenie']);
    }
}
