<?php

namespace App\Controller;


use App\Repository\MovieRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{

    #[Route('/', name: 'movie_home')]
    public function show(MovieRepository $movieRepository,): Response
    {


        $movies = $movieRepository->findAll();
        return $this->render('movie/index.html.twig', [
            'movies' => $movies,

        ]);


    }

}