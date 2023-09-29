<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class AccessDeniedSubscriber implements EventSubscriberInterface
{
    public function onKernelException(ExceptionEvent $event): void
    {
        $request = $event->getRequest();
        if (strpos($request->getPathInfo(), "/back") !== 0) {
            return;
        }

        $exception = $event->getThrowable();
        if (!$exception instanceof AccessDeniedException && !$exception instanceof AccessDeniedHttpException) {
            return;
        }

        $response = new Response("Access Denied", 403);

        // ! json Response version if necessary
        // $response = new JsonResponse([
        //     'error' => $event->getThrowable()->getStatusCode(),
        //     'message' => $event->getThrowable()->getMessage(),
        // ]);

        $event->setResponse($response);
    }

    public static function getSubscribedEvents(): array
    {
        return [
            'kernel.exception' => 'onKernelException',
        ];
    }
}
