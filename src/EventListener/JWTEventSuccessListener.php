<?php
namespace App\EventListener;

use App\Entity\User;
use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationSuccessEvent;
use Symfony\Component\Security\Core\User\UserInterface;

class JWTEventSuccessListener
{
    public function onAuthenticationSuccess(AuthenticationSuccessEvent $event)
    {
        $data = $event->getData();

        /** @var User $user */
        $user = $event->getUser();
        $id = $user->getId();

        if (!$user instanceof UserInterface) {
            return;
        }

        $data['data'] = array(
            'id' => $id,
        );

        // Set the custom data in the response payload
        $event->setData($data);
    }
}
