<?php

namespace App\Twig\Runtime;

use Symfony\Bundle\SecurityBundle\Security;
use  Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\String\Inflector\EnglishInflector;
use Symfony\Component\String\Slugger\SluggerInterface;
use Twig\Extension\RuntimeExtensionInterface;

class AppExtensionRuntime implements RuntimeExtensionInterface
{

    // Lorsque que dans une classe on a pas accés a une entité on l'import dans une fonction constructeur afin de pouvoir avoir accés au methode
    // Exemple je peux pas recuperer mes user dans ce cas là je créer un constructeur et j'injecte la depeendace tokenInterface
    // Pour voir la liste il des service qu'on peut utiliser n'importe ou dans le priojet
    // Il faudra taper la commande symfony console debug:autowiring et on peut mettre le nom de la data qu'on veut recupérer
    private $user;
    private $slugger;

    public function __construct(TokenStorageInterface $user, SluggerInterface $slugger)
    {
        // Inject dependencies if needed
        $this->user = $user;
        $this->slugger = $slugger;
    }

    public function doSomething(int $count, string $singular, ?string $plural = null):string
    {
        //dd($this->user->getToken()->getUser()->getFullName());
        //dd($this->slugger);

        //Composant String de Symfony permettant de traiter des chaine de caractere
        $inflector = new EnglishInflector();

        return $count <= 1 
        ? 
        $count.$plural : 
        $count.$singular;

        // return $count <= 1 
        // ? 
        // $inflector->singularize($plural) : 
        // $count.$singular;
    }
}
