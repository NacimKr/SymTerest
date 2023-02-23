<?php

namespace App\Security;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Http\Authenticator\AbstractLoginFormAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\CsrfTokenBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

class LoginAuthenticator extends AbstractLoginFormAuthenticator
{
    use TargetPathTrait;

    public const LOGIN_ROUTE = 'app_login';

    public function __construct(private UrlGeneratorInterface $urlGenerator)
    {
    }

    //la fonction authenticate retourne un passport -> qui permet de gerer authentifiiccation
    public function authenticate(Request $request): Passport
    {
        // Si la méthode support() est de type POST il sera donc a true
        // Si c'est true on recupere l'email le mot de passe et le csrf_token dan cette méthode voir dd() ci-dessous
        // dd(
        //     $request->request->get('_csrf_token'),
        //     $request->request->get('email'),
        //     $request->request->get('password')
        // );

        //On recupere l'email de l'utilisateur qu'il a saisi
        $email = $request->request->get('email', '');

        //Permet de garder ce que l'utilisateur a saisi sur l'email après la validation du formulaire surtout
        // si il a mal saisi son email afin de le stocké dans l'input

        //On passe en premiere parametre une clé de n'importe quel chaine de caractere puis une variable avec
        //une valeur stocké
        $request->getSession()->set(Security::LAST_USERNAME, $email);

        return new Passport(
            new UserBadge($email),//->UserBadge chercher l'utilisateur par son email
            new PasswordCredentials($request->request->get('password', '')),//-> recuperer le mdp saisi
            [
                new CsrfTokenBadge('authenticate', $request->request->get('_csrf_token')),//->csrf jetton de securité permettant de verifier que le formulaire viens bien de notre site
            ]
        );
        
    }

    // La méthode parle d'elle meme mais je note qu'on peut recupèrer le user avec l'entité tokenIterface 
    //car il y'a la methode getUser() implémenter dans cette interface
    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        //dd($token->getUser()->getFullName());
        $request->getSession()
            ->getFlashBag()
            ->add('success', "Vous etes bien connecté avec succés");

        if ($targetPath = $this->getTargetPath($request->getSession(), $firewallName)) {
            return new RedirectResponse($targetPath);
        }

        // For example:
        return new RedirectResponse($this->urlGenerator->generate('app_home'));
        //throw new \Exception('TODO: provide a valid redirect inside '.__FILE__);
    }

    //Cette méthode recupère url de la page de connexion rempli dans la méthode onAuthenticationSuccess() si l'autentification est réussi
    // si on met une erreur qui est setter dans la methode onAuthenticationFailure()
    protected function getLoginUrl(Request $request): string
    {
        return $this->urlGenerator->generate(self::LOGIN_ROUTE);
    }
}
