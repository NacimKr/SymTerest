<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Http\Event\LogoutEvent;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use  Symfony\Bundle\SecurityBundle\Security;

class LogoutEventSubscriber implements EventSubscriberInterface
{

    //private $urlRedirect;

    public function __construct( 
        private UrlGeneratorInterface $urlRedirect,
        private Security $security
    )
    {
        //$this->urlRedirect = $urlRedirect;
    }

    public function onLogoutEvent(LogoutEvent $event): void
    {
        $event->getRequest()
            ->getSession()
            ->getFlashBag()
            ->add('success', "Vous etes bien deconnecté");

        $this->urlRedirect->generate("app_login");
    }

    public static function getSubscribedEvents(): array
    {
        return [
            LogoutEvent::class => 'onLogoutEvent',
        ];
    }
}
