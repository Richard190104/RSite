<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\Http\Exception\NotFoundException;

/**
 * Public gallery — a category/subcategory browser, only categories flagged
 * Category::show_in_gallery are shown (mirrors the admin's "Show in
 * gallery" checkbox, see Admin\CategoriesController). index() lists the
 * top-level categories as cards; category() drills into one category and
 * shows its subcategories in a row plus the combined photos of the
 * category itself and all of its subcategories below.
 */
class GalleryController extends AppController
{
    public function index(): void
    {
        $categories = $this->fetchTable('Categories')
            ->find()
            ->where(['show_in_gallery' => true, 'parent_id IS' => null])
            ->orderBy(['title' => 'ASC'])
            ->all();

        $this->set(compact('categories'));
        $this->render('cards');
    }

    public function category(string $id): void
    {
        $Categories = $this->fetchTable('Categories');
        $parent = $Categories->find()
            ->where(['show_in_gallery' => true])
            ->where(['id' => $id])
            ->first();

        if ($parent === null) {
            throw new NotFoundException();
        }

        $subcategories = $Categories->find()
            ->where(['parent_id' => $parent->id])
            ->orderBy(['title' => 'ASC'])
            ->all();
        $categoryIds = array_merge([$parent->id], $subcategories->extract('id')->toArray());

        // Categories is contained so each photo's own direct category is
        // available in the template — a photo belonging to one of $parent's
        // subcategories gets a badge naming that subcategory (see
        // templates/Gallery/cards.php), which needs $photo->category->title.
        $photos = $this->fetchTable('Galleries')
            ->find()
            ->contain(['Categories'])
            ->where(['Galleries.category_id IN' => $categoryIds])
            ->orderBy(['Galleries.created' => 'DESC'])
            ->all();

        $this->set(compact('parent', 'subcategories', 'photos'));
        $this->render('cards');
    }
}
