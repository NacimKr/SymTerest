<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Security\EmailVerifier;
use App\Security\LoginAuthenticator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;

class RegistrationController extends AbstractController
{
    private EmailVerifier $emailVerifier;

    public function __construct(EmailVerifier $emailVerifier)
    {
        $this->emailVerifier = $emailVerifier;
    }

    #[Route('/register', name: 'app_register')]
    public function register(
        Request $request, 
        UserPasswordHasherInterface $userPasswordHasher, 
        UserAuthenticatorInterface $userAuthenticator, 
        LoginAuthenticator $authenticator, 
        EntityManagerInterface $entityManager,
    ): Response
    {
        //dd('Send email');

        //Ici on peut mettre le code afin de dire que s'il existe deja il doit pas se réiinscrire

        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // encode the plain password
            //pour recuperer les valeur dans le formType ->get('nom du champs')->getData() pour dire je veux ces données
            //dd($form->get('plainPassword')->getData());
            //ou
            //dd($form["agreeTerms"]->getData());
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form['plainPassword']->getData()
                    )
                );
            
                //A ce niveau là du dd($user) le mot de passe est hasher grâce au setPassword et en recuperant 
                //le champ plainPassword dans le formType
                //dd($user);
                
            $entityManager->persist($user);
            $entityManager->flush();

            // generate a signed url and email it to the user
            // On envoie l'email de confirmation d'inscription en appelant sendEmailCOnfirmation qui prend un route la verification de l'email
            //  et l'objet user en deuxieme parametre et l'objet templatedEmail avec son contenu expediiteur destinataire objet et le contenu sous forme de template twig
            $this->emailVerifier->sendEmailConfirmation('app_verify_email', $user,
                (new TemplatedEmail())
                //Pour accéder aux variable d'environnment on peux soit le fairev via le fichier .env et rappeler la variable avec la methode getenv()
                //ou bien dans le fichier services.yaml (la bonne pratique)
                //et on les recupere avec la methode getParameter()
                    ->from(new Address($this->getParameter('app.mail_from_adress'), $this->getParameter('app.mail_from_name'))) //-> on peut modifier le contenu qu'on souhaite
                    ->to($user->getEmail()) //->on le recpère avec let getEmail de l'entité User ou bien en faisant un $form->get('email')->getData()
                    ->subject('Please Confirm your Email')
                    ->htmlTemplate('registration/confirmation_email.html.twig')
            );
            // do anything else you need here, like send an email

            //On l'authentifie après son inscription garce a la methode authenticateUser()
            return $userAuthenticator->authenticateUser(
                $user,
                $authenticator,
                $request
            );
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

    //Ce lien s'execute lorsqu'on clique sur le lien de confirmation d'email sur le mail envoyé
    #[Route('/verify/email', name: 'app_verify_email')]
    public function verifyUserEmail(Request $request, TranslatorInterface $translator): Response
    {
        //utilisateur est connecté
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        // validate email confirmation link, sets User::isVerified=true and persists
        try {
            $this->emailVerifier->handleEmailConfirmation($request, $this->getUser());//->cette methodfe si l'url du lien de confirmation dans le mail
        } catch (VerifyEmailExceptionInterface $exception) {
            $this->addFlash('verify_email_error', $translator->trans($exception->getReason(), [], 'VerifyEmailBundle'));//->par contyre  si y'a une erreur

            return $this->redirectToRoute('app_register');
        }

        // @TODO Change the redirect on success and handle or remove the flash message in your templates
        $this->addFlash('success', 'Your email address has been verified.');

        return $this->redirectToRoute('app_register');
    }
}
