<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\AppController as BaseController;
use Cake\Event\EventInterface;

/**
 * Base controller for the admin section (/admin/*).
 *
 * Every action here requires a logged in admin user, except the
 * login action itself (whitelisted in beforeFilter below).
 */
class AppController extends BaseController
{
    /**
     * The single source of truth for what's in the admin sidebar — key is
     * the controller name (for routing), value has a translatable label
     * plus a short description of what the section is for and which
     * actions it supports. Read by templates/element/Admin/sidebar.php to
     * render the nav (label only), and by Admin\AiController to tell the
     * chat assistant what sections exist and where to point an admin who
     * asks e.g. "where do I add a news article?". Update this list (not
     * sidebar.php directly, and not by duplicating it elsewhere) when
     * adding a section.
     *
     * @return array<string, array{label: string, description: string, actions: array<int, string>}>
     */
    public static function adminCategories(): array
    {
        return [
            'Dashboard' => [
                'label' => __('Dashboard'),
                'description' => __('The admin landing page — a short overview, no editable content here.'),
                'actions' => ['index'],
            ],
            'Texts' => [
                'label' => __('Texts'),
                'description' => __(
                    'Key/value site-wide text snippets (organisation name, city, address, email, ICO, footer'
                        . ' description, social media URLs...) used throughout the public site. Rows already exist —'
                        . ' this section only edits existing values, it does not create new ones.',
                ),
                'actions' => ['index', 'edit'],
            ],
            'Banners' => [
                'label' => __('Banners'),
                'description' => __(
                    'Images shown on the site: the homepage/page hero carousel, the "about us" mini-banner tiles,'
                        . ' and the "fishing grounds" section image + tiles. Each banner has a location that decides'
                        . ' where it appears, and a title/subtitle.',
                ),
                'actions' => ['index', 'add', 'edit', 'delete'],
            ],
            'NavbarCategories' => [
                'label' => __('Navbar categories'),
                'description' => __(
                    'The dropdown categories shown in the site\'s top navigation menu, each grouping a set of pages.',
                ),
                'actions' => ['index', 'add', 'edit', 'delete'],
            ],
            'Pages' => [
                'label' => __('Pages'),
                'description' => __(
                    'The site\'s static content pages (e.g. kontakt, homepage content) — edits an existing page\'s'
                        . ' text/content; the homepage specifically also has its quick-access shortcuts configured'
                        . ' here.',
                ),
                'actions' => ['index', 'edit'],
            ],
            'News' => [
                'label' => __('News'),
                'description' => __(
                    'News articles shown in the "Latest news" section on the homepage. Each article has a title, a'
                        . ' short plain-text description (shown on the homepage card), an image, a date, an optional'
                        . ' category, and an HTML poster field with an AI assistant that can generate a'
                        . ' notice-board-style graphic from the title/description.',
                ),
                'actions' => ['index', 'add', 'edit', 'delete'],
            ],
            'Categories' => [
                'label' => __('Categories'),
                'description' => __('Categories used to group news articles and gallery items.'),
                'actions' => ['index', 'add', 'edit', 'delete'],
            ],
            'Events' => [
                'label' => __('Events'),
                'description' => __('Events listed on the site.'),
                'actions' => ['index', 'add', 'edit', 'delete'],
            ],
            'Galleries' => [
                'label' => __('Galleries'),
                'description' => __('Photo galleries shown on the site, grouped by category.'),
                'actions' => ['index', 'add', 'edit', 'delete'],
            ],
            'Logos' => [
                'label' => __('Logos'),
                'description' => __('The site\'s logo images (e.g. the main header logo, footer logo).'),
                'actions' => ['index', 'edit'],
            ],
            'Notifications' => [
                'label' => __('Notifications'),
                'description' => __(
                    'Site-wide notifications shown in the navbar\'s bell dropdown when active and within their'
                        . ' valid_from/valid_to date range. A notification can also be flagged to show as a one-off'
                        . ' popup in the corner of the page on load.',
                ),
                'actions' => ['index', 'add', 'edit', 'delete'],
            ],
        ];
    }

    public function initialize(): void
    {
        parent::initialize();

        $this->viewBuilder()->setLayout('admin');
        // zisti, ci je prihlaseny admin user, ak nie, presmeruj na login
        $this->loadComponent('Authentication.Authentication');
    }

    public function beforeFilter(EventInterface $event): void
    {
        parent::beforeFilter($event);

        $this->Authentication->allowUnauthenticated(['login']);
    }
}