<?php

namespace App\Controller\Admin;

use App\Entity\Comment;
use App\Entity\Movie;
use App\Entity\User;
use App\Form\MovieType;
use App\Repository\MovieRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
#[Route('/admin')]
class DashboardController extends AbstractDashboardController
{



    #[Route('/', name: 'admin_home')]
    public function AdminIndex(){
        return $this->render('admin/home.html.twig');

    }
    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Pro Symfony');
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('Dashboard', 'fa fa-home');
        // yield MenuItem::linkToCrud('The Label', 'fas fa-list', EntityClass::class);
    }

    #[Route('/add', name: 'add_movie')]
    public function movie(Request $request, EntityManagerInterface $entityManager)
    {
        // Vérifiez si l'utilisateur actuel a le rôle "admin"
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $movie = new Movie();

        $form = $this->createForm(MovieType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $movie =$form->getData();

            $entityManager->persist($movie);
            $entityManager->flush();

            return $this->redirectToRoute('movie_home');

        }

        return $this->render('admin/publish.html.twig', [

            'form' => $form->createView(),
        ]);
    }
    #[Route('/{id}/edit', name: 'movie_edit')]
    public function editMovie(Request $request, EntityManagerInterface $entityManager, MovieRepository $movieRepository, int $id): Response
    {
        $movie = $movieRepository->find($id);

        if (!$movie) {
            throw $this->createNotFoundException('The movie you are looking to edit does not exist..');
        }

        $form = $this->createForm(MovieType::class, $movie);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $movie = $form->getData();

            $entityManager->persist($movie);
            $entityManager->flush();

            return $this->redirectToRoute('show_movie', ['id' => $movie->getId()]);
        }

        return $this->render('movie/edit.html.twig', [
            'form' => $form->createView(),
            'movie' => $movie,
        ]);

    }

    #[Route('/users', name: 'admin_user_list')]
    public function adminUserList(UserRepository $userRepository): Response
    {
        // Vérifiez si l'utilisateur actuel a le rôle "admin"
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        // Récupérez tous les utilisateurs dans la base de données
        $users = $userRepository->findAll();

        return $this->render('admin/user.html.twig', [
            'users' => $users,
        ]);
    }

    #[Route('/user/delete/{id}', name: 'admin_user_delete')]
    public function deleteUser(Request $request, User $user, EntityManagerInterface $entityManager): Response
    {
        // Vérifiez si l'utilisateur connecté est un admin
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        // Supprimez l'utilisateur
        $entityManager->remove($user);
        $entityManager->flush();

        // Redirigez l'admin vers la liste des utilisateurs
        return $this->redirectToRoute('admin_user_list');
    }

    #[Route('/users/make-admin', name: 'admin_make_admin')]
    public function makeAdmin(Request $request, UserRepository $userRepository,EntityManagerInterface $em): Response
    {


        $userId = $request->request->get('user_id');
        $user = $userRepository->find($userId);
        $user->setRoles(['ROLE_USER','ROLE_ADMIN']);
        $em->flush();

        // Redirect back to the user list page
        return $this->redirectToRoute('admin_user_list');
    }





}
