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
        $Categories = $this->fetchTable('Categories');
        $categories = $Categories->find()
            ->where(['show_in_gallery' => true, 'parent_id IS' => null])
            ->contain(['ChildCategories' => fn ($q) => $q->orderBy(['ChildCategories.position' => 'ASC'])])
            ->orderBy(['position' => 'ASC'])
            ->all();

        $this->applyThumbnailFallbacks($categories);

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
            ->orderBy(['position' => 'ASC'])
            ->all();

        // $parent's own fallback may come from any of its subcategories'
        // photos, so it needs them as "child_categories" the same way
        // index() does; the subcategories themselves have no children of
        // their own (one level of nesting only, see class docblock) so
        // each just falls back to its own photos.
        $parent->child_categories = $subcategories->toArray();
        $this->applyThumbnailFallbacks([$parent]);
        $this->applyThumbnailFallbacks($subcategories);

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

    /**
     * Sets Category::thumbnail_url on each of $categories: the admin
     * `image` when set, otherwise the first photo of the category itself,
     * otherwise (only when $categories carries eager-loaded
     * `child_categories`, as index() does) the first photo among its child
     * categories. Runs a single extra query — one row per category id via
     * a correlated "first photo per category_id" subselect — regardless of
     * how many photos each category actually has.
     */
    private function applyThumbnailFallbacks(iterable $categories): void
    {
        $allIds = [];
        foreach ($categories as $category) {
            $allIds[] = $category->id;
            foreach ($category->child_categories ?? [] as $child) {
                $allIds[] = $child->id;
            }
        }

        $firstPhotoByCategory = $this->firstPhotoByCategory($allIds);

        foreach ($categories as $category) {
            if ($category->image) {
                $category->thumbnail_url = '/img/categories/' . $category->image;
                continue;
            }

            if (isset($firstPhotoByCategory[$category->id])) {
                $category->thumbnail_url = $firstPhotoByCategory[$category->id];
                continue;
            }

            foreach ($category->child_categories ?? [] as $child) {
                if (isset($firstPhotoByCategory[$child->id])) {
                    $category->thumbnail_url = $firstPhotoByCategory[$child->id];
                    break;
                }
            }
        }
    }

    /**
     * Maps each given category id to the image of its earliest (lowest id)
     * photo, via a correlated subselect ("first Galleries.id per
     * category_id") — one query, one row per category regardless of how
     * many photos it actually has.
     */
    private function firstPhotoByCategory(array $categoryIds): array
    {
        if ($categoryIds === []) {
            return [];
        }

        $Galleries = $this->fetchTable('Galleries');
        // select() on a bare column name auto-aliases it (e.g.
        // "Galleries__category_id") unless autoAliasing is disabled — the
        // outer join below needs the plain "category_id"/"first_id" names
        // it asked for, not that auto-generated alias.
        $firstIdPerCategory = $Galleries->find()
            ->disableAutoAliasing()
            ->select(['category_id' => 'Galleries.category_id', 'first_id' => $Galleries->find()->func()->min('id')])
            ->where(['category_id IN' => $categoryIds])
            ->groupBy('category_id');

        $rows = $Galleries->find()
            ->select(['category_id', 'image'])
            ->innerJoin(
                ['first_photo' => $firstIdPerCategory],
                [
                    'Galleries.category_id = first_photo.category_id',
                    'Galleries.id = first_photo.first_id',
                ],
            )
            ->all();

        $map = [];
        foreach ($rows as $row) {
            $map[$row->category_id] = $row->image;
        }

        return $map;
    }
}
