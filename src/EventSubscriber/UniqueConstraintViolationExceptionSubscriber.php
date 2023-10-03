<?php

namespace App\EventSubscriber;

use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;

class UniqueConstraintViolationExceptionSubscriber implements EventSubscriberInterface
{
    public function onKernelException(ExceptionEvent $event): void
    {

        $exception = $event->getThrowable();
        if (!$exception instanceof UniqueConstraintViolationException) {
            return;
        }

        $response = new Response("Cannot create duplicate entries. Please select a different Login code.", 403);

        $event->setResponse($response);
    }

    public static function getSubscribedEvents(): array
    {
        return [
            'kernel.exception' => 'onKernelException',
        ];
    }
}
