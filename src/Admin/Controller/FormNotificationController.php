<?php
namespace App\Admin\Controller;

use App\Entity\Form;
use App\Service\ConfigService;
use App\Service\FormMenuCounterService;
use App\Service\FormNotificationConfigService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class FormNotificationController extends AbstractController
{
    public function __construct(
        private readonly ConfigService $configService,
        private readonly FormMenuCounterService $formMenuCounterService,
        private readonly Security $security,
        private readonly FormNotificationConfigService $formNotificationConfigService,
    )
    {
    }

    #[Route('/admin/forms/{id}/notifications', name: 'admin_forms_notifications')]
    public function notifications(Form $formEntity): Response
    {
        $notificationConfig = $this->formNotificationConfigService->getByFormAndUser($formEntity, $this->security->getUser());

        return $this->render('@Admin/forms/notifications.html.twig', [
            'formEntity' => $formEntity,
            'menuCounts' => $this->formMenuCounterService->getAllCountsByFormId($formEntity->getId()),
            'isBrowserPushNotificationConfigEnabled' => $this->configService->isBrowserPushNotificationsEnabled(),
            'vapidPublicKey' => $this->configService->get(ConfigService::VAPID_PUBLIC_KEY),
            'isBrowserPushNotificationEnabled' => $notificationConfig ? $notificationConfig->getIsBrowserPushEnabled() : false,
        ]);
    }

    #[Route('/admin/api/forms/{id}/notifications/browser-push', name: 'admin_api_forms_notifications_browser_push', methods: ['POST'], format: 'json')]
    public function toggleBrowserPushNotifications(Form $formEntity): Response
    {
        $notificationConfig = $this->formNotificationConfigService->getByFormAndUser($formEntity, $this->security->getUser());
        if (!$notificationConfig) {
            $notificationConfig = $this->formNotificationConfigService->create($formEntity, $this->security->getUser());
        }
        $notificationConfig->setIsBrowserPushEnabled(!$notificationConfig->getIsBrowserPushEnabled());
        $this->formNotificationConfigService->save($notificationConfig);

        return $this->json([
            'isBrowserPushEnabled' => $notificationConfig->getIsBrowserPushEnabled(),
        ]);
    }
}
