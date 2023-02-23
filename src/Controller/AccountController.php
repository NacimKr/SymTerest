<?php

namespace App\Controller;

use App\Form\ChangePasswordFormType;
use App\Form\UserType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/account')]
class AccountController extends AbstractController
{
    #[Route('/', name: 'app_account', methods:"GET")]
    public function show(): Response
    {
        //Controle de securité afin de pas laisser
        //n'importe qui accéder aux pages

        // if(!$this->getUser()){
        //     $this->addFlash("warning", "You need logged in first !");
        //     return $this->redirectToRoute('app_login');
        // }else if(!$this->getUser()->isVerified()){
        //     $this->addFlash("warning", "You need to verified account");
        //     return $this->redirectToRoute('app_home');            
        // }
        
        //OU
        //$this->denyAccessUnlessGranted("ROLE_USER");
        
        $address = strtolower( trim( $this->getUser()->getEmail() ) );

        $hash = md5( $address );

        return $this->render('account/show.html.twig', [
            compact('hash')
        ]);
    }

    #[Route('/edit',  name:"app_edit_account", methods:['GET','POST'])]
    public function edit(Request $request, EntityManagerInterface $em):Response
    {
        //this->getUser() pour recueprer l'utilisateur connecter dans un controller
        $userConnect = $this->getUser();
        $form = $this->createForm(UserType::class, $userConnect);

        //On demande au formulaire créer de gérer la requete pour nous
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $em->persist($userConnect);
            $em->flush();
            $this->addFlash(
                "success",
                "You profile was edited"
            );
            return $this->redirectToRoute("app_account");
        }

        return $this->render('account/edit_account.html.twig', compact('form'));
    }


    #[Route('/edit/password', name: 'app_account_password', methods:["GET","POST"])]
    public function editPassword(Request $request, EntityManagerInterface $em, UserPasswordHasherInterface $passwordEncoder): Response
    {
        $userConnect = $this->getUser();
        $form = $this->createForm(ChangePasswordFormType::class, null, [
            "current_password_is_required" => true //-> current_password_is_required n'est pas connu il faudra aller dans le form type pour configurer l'option dans la méthode configureOption
        ]);

        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            //On recupere le nouveau mot de passe depuis le champ newPassword dans 
            //le form type
            //dd($form->get('newPassword')->getData());
            $hashedPassword = $passwordEncoder->hashPassword(
                $userConnect,
                $form->get('newPassword')->getData()
            );
            $userConnect->setPassword($hashedPassword);

            //dd($userConnect);
            $em->persist($userConnect);
            $em->flush();
            $this->addFlash(
                'success',
                "Your password is modify"
            );
            return $this->redirectToRoute("app_account");
        }

        return $this->render('account/edit_password.html.twig',
            compact('form')
        );
    }
}
