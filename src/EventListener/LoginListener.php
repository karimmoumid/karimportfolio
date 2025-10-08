<?php

// src/EventListener/LoginListener.php

namespace App\EventListener;

use Symfony\Component\Security\Http\Event\LoginSuccessEvent;
use Doctrine\ORM\EntityManagerInterface;

class LoginListener
{
    public function __construct(private EntityManagerInterface $em)
    {
    }

    public function __invoke(LoginSuccessEvent $event)
    {
        $user = $event->getUser();

        if (method_exists($user, 'setLastLogin')) {
            $user->setLastLogin(new \DateTimeImmutable());
            $this->em->flush();
        }
    }
}
