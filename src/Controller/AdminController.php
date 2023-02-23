<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin')]
#[IsGranted('ROLE_ADMIN')]
class AdminController extends AbstractController
{
    #[Route('', name: 'app_admin')]
    public function index(): Response
    {
        // $this->denyAccessUnlessGranted(attribute: 'ROLE_ADMIN', message: "You can not access");

        return $this->render('admin/index.html.twig', [
            'controller_name' => 'AdminController',
        ]);
    }

    #[Route('/pins', name:"app_admin_pins")]
    public function adminPin():Response
    {

        //$this->denyAccessUnlessGranted('ROLE_ADMIN', null, "Not access");

        return $this->render('admin/pin_index.html.twig');
    }
}
