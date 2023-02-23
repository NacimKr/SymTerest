<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;


class SecurityController extends AbstractController
{
    #[Route(path: '/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {

        //cette methode permet de dire que l'utilisateur est deja connecté s'il pense qu'il est deconnecté
        if ($this->getUser()) {
            $this->addFlash('warning', "Vous êtes deja connecté");
            return $this->redirectToRoute('app_home');
        }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    #[Route(path: '/logout', name: 'app_logout', methods:['POST','PUT'])]
    public function logout(Request $request): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');

        $submittedToken = $request->request->get('token');

        if($this->isCsrfTokenValid('logout', $submittedToken)){

        }
    }
}
