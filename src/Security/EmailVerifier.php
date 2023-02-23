<?php

namespace App\Security;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;
use SymfonyCasts\Bundle\VerifyEmail\VerifyEmailHelperInterface;

class EmailVerifier
{
    public function __construct(
        private VerifyEmailHelperInterface $verifyEmailHelper, //->
        private MailerInterface $mailer, //-> composant d'email pour envyer des email simple
        private EntityManagerInterface $entityManager //->
    ) {
        //dd($verifyEmailHelper);
    }
    
    public function sendEmailConfirmation(string $verifyEmailRouteName, 
    UserInterface $user, TemplatedEmail $email): void
    {
        //dd($this->verifyEmailHelper);
        $signatureComponents = $this->verifyEmailHelper->generateSignature(
            $verifyEmailRouteName,
            $user->getId(),
            $user->getEmail()
        );

        //dd($signatureComponents);

        //on passe des context pour donner différentes données au template de l'email donc le getContext permet de les recuperer et on set les differente variable signedURL etc... puis on les envoie avec la méthode send()
        //Pour les afficher dans le confirmation.html.twig
        $context = $email->getContext();
        //dd($context);
        //Ces ligne ci-dessous permet d'avoir un lien securiser avec une url securiser et un temps dexpiration pour le lien de confirmation sur l'email
        $context['signedUrl'] = $signatureComponents->getSignedUrl();
        $context['expiresAtMessageKey'] = $signatureComponents->getExpirationMessageKey();
        $context['expiresAtMessageData'] = $signatureComponents->getExpirationMessageData();

        $email->context($context);

        $this->mailer->send($email);
    }

    /**
     * @throws VerifyEmailExceptionInterface
     */
    public function handleEmailConfirmation(Request $request, 
    UserInterface $user): void
    {
        $this->verifyEmailHelper->validateEmailConfirmation($request->getUri(), $user->getId(), $user->getEmail());

        $user->setIsVerified(true);

        $this->entityManager->persist($user);
        $this->entityManager->flush();
    }
}
