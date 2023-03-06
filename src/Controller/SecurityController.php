<?php

namespace App\Controller;

use App\Form\LoginType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    #[Route(path: '/login', name: 'app_user_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        $form = $this->createForm(LoginType::class);


        if ($form->isSubmitted() && $form->isValid()){
            return $this->redirectToRoute('movie_home');
        }

        return $this->render('security/login.html.twig', [
            'form' => $form->createView(),
        ]);

    }

    #[Route(path: '/logout', name: 'app_user_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');

    }
}
