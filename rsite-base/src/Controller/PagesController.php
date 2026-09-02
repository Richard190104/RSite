<?php
declare(strict_types=1);

/**
 * CakePHP(tm) : Rapid Development Framework (https://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 * @link      https://cakephp.org CakePHP(tm) Project
 * @since     0.2.9
 * @license   https://opensource.org/licenses/mit-license.php MIT License
 */
namespace App\Controller;

use Cake\Core\Configure;
use Cake\Http\Exception\ForbiddenException;
use Cake\Http\Exception\NotFoundException;
use Cake\Http\Response;
use Cake\I18n\Date;
use Cake\View\Exception\MissingTemplateException;

/**
 * Static content controller
 *
 * This controller will render views from templates/Pages/
 *
 * @link https://book.cakephp.org/4/en/controllers/pages-controller.html
 */
class PagesController extends AppController
{
    /**
     * The homepage. For now just fetches the fixed "home" page row (title +
     * content: about_us_text / quick_access) — see Admin\PagesController::
     * editHome() for how the content is edited. Rendering comes later.
     */
    public function home(): void
    {
        $page = $this->fetchTable('Pages')->find()->where(['slug' => 'home'])->firstOrFail();

        $this->set(compact('page'));
    }

    /**
     * Public contact page. Contact fields come from Texts (admin); the page
     * row itself is the fixed "kontakt" slug used by the navbar button.
     */
    public function kontakt(): void
    {
        $page = $this->fetchTable('Pages')->find()->where(['slug' => 'kontakt'])->firstOrFail();

        $this->set(compact('page'));
    }

    /**
     * Public activities page: upcoming event cards, activity categories, and
     * a front-end calendar fed by all Events from admin.
     */
    public function aktivity(): void
    {
        $page = $this->fetchTable('Pages')->find()->where(['slug' => 'aktivity'])->firstOrFail();
        $today = Date::now();

        $Events = $this->fetchTable('Events');

        $upcomingEvents = $Events->find()
            ->contain(['Categories'])
            ->where(['Events.date >=' => $today])
            ->orderBy(['Events.date' => 'ASC'])
            ->limit(3)
            ->all()
            ->toList();

        $allEvents = $Events->find()
            ->contain(['Categories'])
            ->where(['Events.date IS NOT' => null])
            ->orderBy(['Events.date' => 'ASC'])
            ->all()
            ->toList();

        $categoryIds = $Events->find()
            ->select(['category_id'])
            ->where(['category_id IS NOT' => null])
            ->distinct(['category_id'])
            ->all()
            ->extract('category_id')
            ->toList();

        $categories = $categoryIds
            ? $this->fetchTable('Categories')
                ->find()
                ->where(['id IN' => $categoryIds])
                ->orderBy(['title' => 'ASC'])
                ->limit(5)
                ->all()
                ->toList()
            : [];

        $calendarEvents = array_map(static function ($event) {
            return [
                'id' => $event->id,
                'title' => $event->title,
                'description' => $event->description,
                'date' => $event->date?->format('Y-m-d'),
                'location' => $event->location,
                'time' => $event->time,
            ];
        }, $allEvents);

        $this->set(compact('page', 'upcomingEvents', 'categories', 'calendarEvents'));
    }

    /**
     * Displays a view
     *
     *
     * @param string ...$path Path segments.
     * @return \Cake\Http\Response|null
     * @throws \Cake\Http\Exception\ForbiddenException When a directory traversal attempt.
     * @throws \Cake\View\Exception\MissingTemplateException When the view file could not
     *   be found and in debug mode.
     * @throws \Cake\Http\Exception\NotFoundException When the view file could not
     *   be found and not in debug mode.
     * @throws \Cake\View\Exception\MissingTemplateException In debug mode.
     */
    public function display(string ...$path): ?Response
    {
        if (!$path) {
            return $this->redirect('/');
        }
        if (in_array('..', $path, true) || in_array('.', $path, true)) {
            throw new ForbiddenException();
        }
        $page = $subpage = null;

        if (!empty($path[0])) {
            $page = $path[0];
        }
        if (!empty($path[1])) {
            $subpage = $path[1];
        }

        $pageEntity = $this->fetchTable('Pages')->find()->where(['slug' => $page])->first();
        if ($pageEntity !== null) {
            $page = $pageEntity;
        }

        $this->set(compact('page', 'subpage'));

        try {
            return $this->render(implode('/', $path));
        } catch (MissingTemplateException $exception) {
            if (Configure::read('debug')) {
                throw $exception;
            }
            throw new NotFoundException();
        }
    }
}
