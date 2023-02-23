<?php

namespace App\Security\Voter;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class PinVoter extends Voter
{
    public const EDIT = 'PIN_EDIT';
    public const CREATE = 'PIN_CREATE';
    public const DELETE = "PIN_DELETE";

    //Cette méthode nous permet de dire est-ce que cette méthode doit être appliqué
    //Si elle retourne true alors la methode voteOnAttribute va être appellée
    //Si ça retourne false rien va se passer
    protected function supports(string $attribute, mixed $subject): bool
    {
        //dd($attribute, $subject);
        // replace with your own logic
        // https://symfony.com/doc/current/security/voters.html

        //si l'attribut fait partie de l'un de ces valeur alors on instance l'objet
        return in_array($attribute, ["MANAGE"])
            && $subject instanceof \App\Entity\Pin;
    }

    protected function voteOnAttribute(string $attribute, mixed $mypin, TokenInterface $token): bool
    {
        $user = $token->getUser();
        //dd($attribute);
        // if the user is anonymous, do not grant access
        if (!$user instanceof UserInterface) {
            return false;
        }

        // ... (check conditions and return true to grant permission) ...
        switch ($attribute) {
            case "MANAGE":
                return $user->isVerified() && $user === $mypin->getUser();               // logic to determine if the user can EDIT
                // return true or false
                break;

            // case self::CREATE:
            //     return $user->isVerified() && $user === $mypin->getUser(); 
            //     break;

            // case self::DELETE:
            //     return $user->isVerified() && $user === $mypin->getUser(); 
            //     break;
        }

        return false;
    }
}
