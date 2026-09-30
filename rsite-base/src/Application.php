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
 * @since     3.3.0
 * @license   https://opensource.org/licenses/mit-license.php MIT License
 */
namespace App;

use Authentication\AuthenticationServiceInterface;
use Authentication\AuthenticationServiceProviderInterface;
use Authentication\Middleware\AuthenticationMiddleware;
use Cake\Core\Configure;
use Cake\Core\ContainerInterface;
use Cake\Datasource\FactoryLocator;
use Cake\Error\Middleware\ErrorHandlerMiddleware;
use Cake\Http\BaseApplication;
use Cake\Http\Middleware\BodyParserMiddleware;
use Cake\Http\Middleware\CsrfProtectionMiddleware;
use Cake\Http\MiddlewareQueue;
use Cake\ORM\Locator\TableLocator;
use Cake\ORM\TableRegistry;
use Cake\Routing\Middleware\AssetMiddleware;
use Cake\Routing\Middleware\RoutingMiddleware;
use App\Http\Middleware\RejectOversizedUploadMiddleware;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Application setup class.
 *
 * This defines the bootstrapping logic and middleware layers you
 * want to use in your application.
 *
 * @extends \Cake\Http\BaseApplication<\App\Application>
 */
class Application extends BaseApplication implements AuthenticationServiceProviderInterface
{
    /**
     * Load all the application configuration and bootstrap logic.
     *
     * @return void
     */
    public function bootstrap(): void
    {
        // Call parent to load bootstrap from files.
        parent::bootstrap();

        if (PHP_SAPI === 'cli') {
            $this->bootstrapCli();
        } else {
            FactoryLocator::add(
                'Table',
                (new TableLocator())->allowFallbackClass(false)
            );
        }

        /*
         * Only try to load DebugKit in development mode
         * Debug Kit should not be installed on a production system
         */
        if (Configure::read('debug')) {
            $this->addPlugin('DebugKit');
        }

        $this->addPlugin('Authentication');

        // Shared admin/site infrastructure reused across client projects —
        // starts minimal (no bootstrap/routes of its own yet), see
        // vendor/richard190104/rcore's README for what's in it so far.
        $this->addPlugin('Rcore');

        // Extends the shared Rcore Configurations (color palette) admin
        // screen with this app's own bits, without patching plugin code —
        // see vendor/richard190104/rcore's README for what these do.
        Configure::write('Rcore.extraColorGroups', [
            'Fishing grounds map' => ['reviry_map_bg' => __('Map background')],
        ]);
        Configure::write('Rcore.configurationsExtraElements', ['Admin/automaticImagesToggle']);

        // Domain wording substituted into the shared AI assistant's
        // prompts — see vendor/richard190104/rcore's README for the other
        // usages of these two keys.
        Configure::write('Rcore.aiOrganisationDescription', 'a local fishing association (MO SRZ)');
        Configure::write('Rcore.aiAssistantName', 'Rybárik');

        // Lets the AI assistant's navigation-helper mode resolve "where's
        // the article about X" questions into a News edit link, the same
        // way it already resolves Rcore.Texts rows — without the plugin
        // itself ever needing to know this app has a News table. Capped at
        // 20 rows so the prompt's token cost doesn't grow with the site's
        // article count; an admin asking about an older article not in
        // this list gets an honest "couldn't find it" (see notFoundHint)
        // instead of a wrong answer.
        Configure::write('Rcore.aiNavigationContextProviders', [
            [
                'prefix' => 'news',
                'tableAlias' => 'News',
                'controller' => 'News',
                'targetHint' => 'ONLY when the admin is asking about one specific existing News article (by title'
                    . ' or by something mentioned in its description) and you can identify exactly which one from'
                    . ' the News list below.',
                'promptIntro' => 'Here is a list of the 20 most recent News articles (id, title, description) — NOT'
                    . ' the complete list, older articles may exist that aren\'t shown here. Use these ids for'
                    . ' "target" when the question is about one of these specific articles:',
                'lines' => function (): array {
                    $rows = TableRegistry::getTableLocator()->get('News')
                        ->find()
                        ->select(['id', 'title', 'description'])
                        ->orderBy(['date' => 'DESC'])
                        ->limit(20)
                        ->all();
                    $lines = [];
                    foreach ($rows as $article) {
                        $lines[] = "- news:{$article->id} — \"{$article->title}\": {$article->description}";
                    }

                    return $lines;
                },
                'notFoundHint' => 'If a question is about a News article you can\'t find in that list, it may simply'
                    . ' be older than what\'s shown — say so honestly (e.g. suggest checking the News section\'s full'
                    . ' list) instead of guessing an id or claiming the article doesn\'t exist at all.',
            ],
        ]);

        // This app's own admin sidebar sections, merged onto the shared
        // Rcore plugin's own (Texts, Configurations) by
        // Rcore\Controller\Admin\AppController::adminCategories(). Update
        // this list (not sidebar.php, which now lives in the plugin) when
        // adding a section.
        Configure::write('Rcore.extraAdminCategories', [
            'Dashboard' => [
                'label' => __('Dashboard'),
                'description' => __('The admin landing page — a short overview, no editable content here.'),
                'actions' => ['index'],
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
            'CommitteeMembers' => [
                'label' => __('Committee'),
                'description' => __(
                    'The organisation\'s committee (výbor) — name (required), plus optional phone, email, and a'
                        . ' photo. Not shown on the public site yet, admin-managed data only for now.',
                ),
                'actions' => ['index', 'add', 'edit', 'delete'],
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
            'FishingGrounds' => [
                'label' => __('Revíry'),
                'description' => __(
                    'The individual fishing grounds/territories (revíry) the organisation manages — each with a'
                        . ' title, description, photo, free-text location, and map coordinates. Separate from the'
                        . ' "reviry" static page text (that\'s edited under Pages) — this is the actual list of'
                        . ' waters.',
                ),
                'actions' => ['index', 'add', 'edit', 'delete'],
            ],
            'Galleries' => [
                'label' => __('Galleries'),
                'description' => __('Photo galleries shown on the site, grouped by category.'),
                'actions' => ['index', 'add', 'edit', 'delete'],
            ],
            'Logos' => [
                'label' => __('Logos'),
                'description' => __('The site\'s logo images: the header logo, plus partner logos in the footer once an image is uploaded.'),
                'actions' => ['index', 'add', 'edit', 'delete'],
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
        ]);
    }

    /**
     * Setup the middleware queue your application will use.
     *
     * @param \Cake\Http\MiddlewareQueue $middlewareQueue The middleware queue to setup.
     * @return \Cake\Http\MiddlewareQueue The updated middleware queue.
     */
    public function middleware(MiddlewareQueue $middlewareQueue): MiddlewareQueue
    {
        $middlewareQueue
            // Catch any exceptions in the lower layers,
            // and make an error page/response
            ->add(new ErrorHandlerMiddleware(Configure::read('Error'), $this))

            // Handle plugin/theme assets like CakePHP normally does.
            ->add(new AssetMiddleware([
                'cacheTime' => Configure::read('Asset.cacheTime'),
            ]))

            // Add routing middleware.
            // If you have a large number of routes connected, turning on routes
            // caching in production could improve performance.
            // See https://github.com/CakeDC/cakephp-cached-routing
            ->add(new RoutingMiddleware($this))

            // Parse various types of encoded request bodies so that they are
            // available as array through $request->getData()
            // https://book.cakephp.org/4/en/controllers/middleware.html#body-parser-middleware
            ->add(new BodyParserMiddleware())

            // Before CSRF: oversized multipart uploads empty $_POST (incl. the
            // CSRF token) and would otherwise look like a token failure.
            ->add(new RejectOversizedUploadMiddleware())

            // Cross Site Request Forgery (CSRF) Protection Middleware
            // https://book.cakephp.org/4/en/security/csrf.html#cross-site-request-forgery-csrf-middleware
            ->add(new CsrfProtectionMiddleware([
                'httponly' => true,
            ]))

            // Identifies the currently logged in user (if any) from the session,
            // used by the Admin\AppController to gate access to /admin/*.
            ->add(new AuthenticationMiddleware($this));

        return $middlewareQueue;
    }

    /**
     * Returns a service provider instance.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request Request
     * @return \Authentication\AuthenticationServiceInterface
     */
    
    public function getAuthenticationService(ServerRequestInterface $request): AuthenticationServiceInterface
    {
        return \Rcore\Auth\AdminAuthenticationServiceFactory::build();
    }

    /**
     * Register application container services.
     *
     * @param \Cake\Core\ContainerInterface $container The Container to update.
     * @return void
     * @link https://book.cakephp.org/4/en/development/dependency-injection.html#dependency-injection
     */
    public function services(ContainerInterface $container): void
    {
    }

    /**
     * Bootstrapping for CLI application.
     *
     * That is when running commands.
     *
     * @return void
     */
    protected function bootstrapCli(): void
    {
        $this->addOptionalPlugin('Bake');

        $this->addPlugin('Migrations');

        // Load more plugins here
    }
}
