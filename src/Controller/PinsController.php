<?php

namespace App\Controller;

use App\Entity\Pin;
use App\Entity\User;
use App\Form\PinType;
use App\Repository\PinRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
// use Symfony\Component\Form\Extension\Core\Type\TextareaType;
// use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class PinsController extends AbstractController
{
    // private $em;

    // public function __construct(EntityManagerInterface $em){
    //     $this->em = $em;
    // }

    #[Route('/', name: 'app_home', methods:['GET'])]
    public function index(PinRepository $pinRepository, UserRepository $userRepository): Response
    {
        //dd($this->getUser()->getId());
        //dd(Pin::NUM_ITEMS_PER_PAGE);

        $pins = $pinRepository->findBy([
            //"email" => $this->getUser()->getEmail()
        ], ["createdAt" => "DESC"]);
        
        return $this->render('pins/index.html.twig', 
            compact('pins')
        );
    }



    #[Route('/create/pins', name:"app_create",  methods:['GET','POST'])]
    // #[Security("is_granted('ROLE_USER') and user.isVerified()")]
    //ou on passe par le voter qu'on vient de créer
    #[Security("is_granted('MANAGE', pin)")]
    public function create(Request $request, EntityManagerInterface $em, UserRepository $userRepository):Response
    {
        
        /* 
            Pour reduire l'ecriture de la ligne ci-dessous
            on peut utiliser l'annotation Security qui est au-dessus où
            pn verifie chaque rol et les propriétes si elle sont correct 
        */

        // if(!$this->getUser()){
        //     $this->addFlash('warning', "You need to log in !");
        //     return $this->redirectToRoute("app_login");
        // }elseif(!$this->getUser()->isVerified()){
        //     $this->addFlash('warning', "You need to verified your email !");
        //     return $this->redirectToRoute("app_home");
        // }

        //$this->denyAccessUnlessGranted("ROLE_USER",null, "Vous devez verifier votre compte");

        if(!$this->getUser()){
            throw $this->createAccessDeniedException("You cannot access to this page");
        }

        // if(!$this->getUser()){
        //     throw $this->createNotFoundException("You cannot access to this page");
        // }

        $pin = new Pin();
        // $pin->setTitle('blabla')
        //     ->setDescription('blabla2');

        $form = $this->createForm(PinType::class, $pin);
        
        //dd($userRepository->findBy(['id' => 73]));

        //On recupère les données du formlaire via une requete
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            //dd($this->getUser());
            // $data = $form->getData();
            // dd($data);
            // $pin->setTitle($data->getTitle())
            //     ->setDescription($data->getDescription());
            $this->addFlash(
                'success',
                'Pin successfully created!'
            );
            //$pin->setUser($userRepository->findOneBy(['id' => 73]));
            $pin->setUser($this->getUser());
            $em->persist($pin);
            $em->flush();
            
            return $this->redirectToRoute('app_home');
        }

        return $this->render('pins/create.html.twig',[
            "form" => $form
        ]);
    }



    #[Route('/show/pins/{id}', name: 'app_show', methods:['GET'])]
    // #[Security('is_granted("ROLE_USER") && user.isVerified()')]
    public function show(Pin $pin): Response
    {
        // if(!$this->getUser()){
        //     $this->addFlash('warning', "You need to log in !");
        //     return $this->redirectToRoute("app_login");
        // }elseif(!$this->getUser()->isVerified()){
        //     $this->addFlash('warning', "You need to verified !");
        //     return $this->redirectToRoute("app_home");
        // }

        // if(!$pin){
        //     throw $this->createNotFoundException("Pins introuvable !");
        // }

        return $this->render('pins/show.html.twig',[
            'pin' => $pin
        ]);
    }



    #[Route('/edit/pins/{id<[0-9]+>}', name:"app_edit", methods:['GET','POST'])]
    //PIN_EDIT est une permission qu'on vient de créer du coup faudra qu'on créer un voter pour gérer PIN_EDIT
    #[Security('is_granted("MANAGE", pin)')]
    public function edit(Pin $pin, Request $request, EntityManagerInterface $em):Response
    {

        // if(!$this->getUser()){
        //     $this->addFlash('warning', "You need to log in !");
        //     return $this->redirectToRoute("app_login");
        // }elseif(!$this->getUser()->isVerified()){
        //     $this->addFlash('warning', "You need to verified !");
        //     return $this->redirectToRoute("app_home");
        // }

        //Là on verifie si l'utilisateur est différent de celui qui est connecté
        //Donc check si on est bien l'auteur du pin qu'on veut modifier sinon on le redirige vers l'accueil
        // if($this->getUser() != $pin->getUser()){
        //     $this->addFlash('danger', "You are not a author of that pins");
        //     return $this->redirectToRoute("app_home");
        // }

        $form = $this->createForm(PinType::class, $pin);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {     

            $em->persist($pin);
            $em->flush();
            $this->addFlash(
                'warning', 
                'Pin updated with success!'
            );
            return $this->redirectToRoute('app_home');
        }

        return $this->render('pins/edit.html.twig', compact('form', "pin"));
    }


    
    #[Route('/pins/delete/{id<[0-9]+>}', name:"app_delete", methods:['GET','DELETE'])]
    // #[Security('is_granted("ROLE_USER") && user.isVerified() && pin.getUser() === user')]
    //ou on passe par le voter qu'on de créer
    #[Security('is_granted("MANAGE", pin)')]
    public function delete(Request $request, Pin $pin, EntityManagerInterface $em)
    {

        if(!$this->getUser()){
            $this->addFlash('warning', "You need to log in !");
            return $this->redirectToRoute("app_login");
        }elseif(!$this->getUser()->isVerified()){
            $this->addFlash('warning', "You need to verified !");
            return $this->redirectToRoute("app_home");
        }

        //Vérification de si on est l'auteur ou pas du pin qu'on veut supprimer
        if($this->getUser() != $pin->getUser()){
            $this->addFlash('danger', "You are not a author of that pins");
            return $this->redirectToRoute("app_home");
        }

        $em->remove($pin);
        $em->flush();
        $this->addFlash(
            'danger', 
            'Pin deleted with success!'
        );
        return $this->redirectToRoute('app_home');
    }
}
