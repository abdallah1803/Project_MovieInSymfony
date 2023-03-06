<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegisterType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
//use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class RegistrationController extends AbstractController
{

    /*/**
     * @param Request $request
     * @param UserPasswordEncoderInterface $passwordEncoder
     * @return RedirectResponse|Response
     */

    #[Route('/register', name: 'app_register')]

    public function register(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordEncoder)
    {

        $user = new User();
        $form = $this->createForm(RegisterType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user =$form->getData();


            $user->setPassword($passwordEncoder->hashPassword($user,$user->getPlainPassword()));



            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('app_user_login');

        }

        return $this->render('registration/register.html.twig', [
            'form' => $form->createView(),



        ]);
    }
}
