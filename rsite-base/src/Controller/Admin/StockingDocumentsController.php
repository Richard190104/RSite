<?php
declare(strict_types=1);

namespace App\Controller\Admin;

class StockingDocumentsController extends AppController
{
    use FileUploadTrait;

    public function add()
    {
        $StockingDocuments = $this->fetchTable('StockingDocuments');
        $stockingDocument = $StockingDocuments->newEmptyEntity();

        if ($this->request->is('post')) {
            $data = $this->request->getData();
            /** @var \Psr\Http\Message\UploadedFileInterface|null $upload */
            $upload = $data['file'] ?? null;
            unset($data['file']);

            $hasFile = $upload !== null && $upload->getError() !== UPLOAD_ERR_NO_FILE;
            $uploadError = $this->fileUploadError($upload, true);

            $stockingDocument = $StockingDocuments->patchEntity($stockingDocument, $data);

            if (!$stockingDocument->getErrors() && $uploadError === null) {
                $stockingDocument->file = $this->storeFileUpload($upload, 'stocking-documents');

                if ($StockingDocuments->save($stockingDocument)) {
                    $this->Flash->success(__('Document saved.'));

                    return $this->redirect(['prefix' => 'Admin', 'controller' => 'Pages', 'action' => 'edit', 'zarybnenie']);
                }
            }

            if ($uploadError !== null) {
                $this->Flash->error($uploadError);
            }
            $this->Flash->error(__('Could not save the document, check the errors below.'));
        }

        $this->set('stockingDocument', $stockingDocument);

        return null;
    }

    public function edit(?string $id = null)
    {
        $StockingDocuments = $this->fetchTable('StockingDocuments');
        $stockingDocument = $StockingDocuments->get($id);

        if ($this->request->is(['post', 'put'])) {
            $data = $this->request->getData();
            /** @var \Psr\Http\Message\UploadedFileInterface|null $upload */
            $upload = $data['file'] ?? null;
            unset($data['file']);

            $hasNewFile = $upload !== null && $upload->getError() !== UPLOAD_ERR_NO_FILE;
            $uploadError = $hasNewFile ? $this->fileUploadError($upload, false) : null;

            $oldFile = $stockingDocument->file;
            $stockingDocument = $StockingDocuments->patchEntity($stockingDocument, $data);

            if (!$stockingDocument->getErrors() && $uploadError === null) {
                if ($hasNewFile) {
                    $stockingDocument->file = $this->storeFileUpload($upload, 'stocking-documents');
                }

                if ($StockingDocuments->save($stockingDocument)) {
                    if ($hasNewFile && $oldFile) {
                        $this->deleteFileUpload('stocking-documents', $oldFile);
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

        $this->set('stockingDocument', $stockingDocument);

        return null;
    }

    public function delete(?string $id = null)
    {
        $this->request->allowMethod(['post', 'delete']);

        $StockingDocuments = $this->fetchTable('StockingDocuments');
        $stockingDocument = $StockingDocuments->get($id);

        if ($StockingDocuments->delete($stockingDocument)) {
            $this->deleteFileUpload('stocking-documents', $stockingDocument->file);
            $this->Flash->success(__('Document deleted.'));
        } else {
            $this->Flash->error(__('Could not delete the document.'));
        }

        return $this->redirect(['prefix' => 'Admin', 'controller' => 'Pages', 'action' => 'edit', 'zarybnenie']);
    }
}
