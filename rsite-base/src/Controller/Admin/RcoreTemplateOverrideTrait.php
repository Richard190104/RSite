<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use Cake\Event\EventInterface;
use Cake\View\View;

/**
 * For the core-owned pass-through controllers (Dashboard, Pages, Logos,
 * Notifications, Categories, NavbarCategories, Galleries, Banners): their
 * parent's templates live entirely in the plugin and never call the
 * AiAssistant widget themselves — a shared template can't assume every
 * project registered that helper.
 *
 * Rather than keeping an app-side copy of each of those templates just to
 * add one line (every future fix/improvement to the plugin's own templates
 * would then need re-copying by hand here too), this appends the widget's
 * HTML straight onto the plugin template's already-rendered output via
 * View.afterRenderFile — a listener on that event can return modified
 * content in place of what a template file produced
 * (see Cake\View\View::_render()). The plugin's templates themselves are
 * never touched or duplicated.
 *
 * Skips any template that already rendered its own widget (bespoke pages
 * like Pages::editHome() keep their own explicit
 * `<?= $this->AiAssistant->widget(...) ?>` call, each with its own field
 * config tailored to that page) — detected by checking the output doesn't
 * already contain the widget's own marker class, so this never
 * double-renders it.
 */
trait RcoreTemplateOverrideTrait
{
    /**
     * Per-action field-drafting config (same shape as
     * AiAssistantHelper::widget()'s own $options) for actions whose plugin
     * template should get more than the plain navigation-only widget.
     * Override this method in the controller using this trait, keyed by
     * action name — see Admin\NotificationsController for a working
     * example. A property instead of a method would hit a fatal PHP error
     * here: a class and a trait it uses can't both declare the same
     * property with different default values.
     *
     * @return array<string, array<string, mixed>>
     */
    protected function aiChatFieldsByAction(): array
    {
        return [];
    }

    public function beforeRender(EventInterface $event): void
    {
        parent::beforeRender($event);

        $action = (string)$this->request->getParam('action');
        $fieldsByAction = $this->aiChatFieldsByAction();
        if (isset($fieldsByAction[$action])) {
            $this->set('aiChatFields', $fieldsByAction[$action]);
        }

        $this->getEventManager()->on(
            'View.afterRenderFile',
            function (EventInterface $event, string $templateFile, string $content): ?string {
                /** @var \Cake\View\View $view */
                $view = $event->getSubject();
                if (
                    $view->getCurrentType() !== View::TYPE_TEMPLATE
                    || !$view->helpers()->has('AiAssistant')
                    || str_contains($content, 'admin-ai-chat')
                ) {
                    return null;
                }

                return $content . $view->helpers()->get('AiAssistant')->widget($view->get('aiChatFields') ?? []);
            },
        );
    }
}
