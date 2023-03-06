<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Favorite;
use App\Entity\Movie;
use App\Entity\User;
use App\Form\CommentType;
use App\Form\MovieSearchType;
use App\Form\MovieType;
use App\Form\RegisterType;


use App\Repository\CommentRepository;

use App\Repository\FavoriteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\MovieRepository;
class MovieController extends AbstractController {



    #[Route('movie/{id}/delete', name: 'movie_delete')]
    public function deleteMovie(Request $request, EntityManagerInterface $entityManager, Movie $movie, ): Response
    {
        $entityManager->remove($movie);
        $entityManager->flush();

        $this->addFlash('success', 'Le film a été supprimé avec succès.');

        return $this->redirectToRoute('movie_home');
    }

    #[Route('movie/comment/{id}', name: 'delete_comment')]
    public function deleteComment( int $id, EntityManagerInterface $entityManager): Response
    {
        $comment = $entityManager->getRepository(Comment::class)->find($id);

        if (!$comment) {
            throw $this->createNotFoundException('Comment not found');
        }

        if ($comment->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException('You are not authorized to delete this comment');
        }

        $entityManager->remove($comment);
        $entityManager->flush();

        return $this->redirectToRoute('show_movie', ['id' => $comment->getMovie()->getId()]);
    }


    #[Route("movie/{id}/toggle", name:'favorite_movie')]
    public function toggle(Movie $film, EntityManagerInterface $entityManager)
    {
        $user = $this->getUser(); // récupérer l'utilisateur actuellement connecté
        $favori = $entityManager->getRepository(Favorite::class)->findOneBy(['User' => $user, 'Movie' => $film]);

        if ($favori) {
            $entityManager->remove($favori);
            $entityManager->flush();
        } else {

            $favori = new Favorite();
            $favori->setMovie($film);
            $favori->setUser($this->getUser());
            $entityManager->persist($favori);
            $entityManager->flush();
        }



        return $this->redirectToRoute('show_movie', ['id' => $film->getId()]);
    }

    #[Route('movie/favorites', name: 'favorite_movies')]
    public function favoriteMovies(EntityManagerInterface $entityManager, FavoriteRepository $favoriteRepository): Response
    {
        $user = $this->getUser();
        $favorites = $favoriteRepository->findBy(['User' => $user]);

        return $this->render('movie/favorites.html.twig', [
            'favorites' => $favorites,
        ]);
    }




    #[Route('/movie/{id}', name: 'show_movie')]
    public function showMovie(Request $request, EntityManagerInterface $entityManager, Movie $movie, CommentRepository $commentRepository ): Response
    {
        $isLogin = $this->isGranted('IS_AUTHENTICATED_FULLY');
        $comment = new Comment();
        $form = $this->createForm(CommentType::class);
        $form->handleRequest($request);

        $user = $this->getUser();
        $comments = $commentRepository->findBy(
            ['Movie' => $movie],
            ['createdAt' => 'DESC']
        );






        if ($form->isSubmitted() && $form->isValid()) {
            $comment = $form->getData();
            $comment->setCreatedAt(new \DateTimeImmutable());
            $comment->setMovie($movie);
            $comment->setUser($this->getUser());
            $comment->setAuthor($user->getUsername());

            $entityManager->persist($comment);
            $entityManager->flush();

            return $this->redirectToRoute('show_movie', ['id' => $movie->getId()]);
        }

        return $this->render('movie/movie.html.twig', [
            'movie' => $movie,
            'comments' => $comments,
            'form' => $form->createView(),
            'isLogin' => $isLogin,


        ]);

    }






}
