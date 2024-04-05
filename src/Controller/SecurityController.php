<?php

namespace App\Controller;


use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

final Class SecurityController extends AbstractController 
{ 
    #[Route('/login',name: 'login', methods:['GET', 'POST'])]
    public function login(AuthenticationUtils $utils): Response
    {
        // Cette fonction ramène une réponse en cas d'erreur d'authentification avec la method getsLastAuthenticationError(), dans ce cas la, avec la method getLastUsernam(), le der nier username est récupéré pour éviter à l'utilisateur de retaper son identifiant.
       
            return $this->render('login.html.twig', [
            'error' => $utils->getLastAuthenticationError(),
            'last_username' => $utils->getLastUsername(),
        ]);
    }
}