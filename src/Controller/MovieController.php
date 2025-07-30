<?php

namespace App\Controller;

use App\Entity\Movie;
use App\Entity\User;
use App\Entity\Vote;
use App\Form\MovieType;
use App\Repository\MovieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;

class MovieController extends AbstractController
{
    #[Route('/', name: 'movie_index')]
    public function index(Request $request, MovieRepository $movieRepository, PaginatorInterface $paginator): Response
    {
        $sortBy = $request->query->get('sort', 'created_at');
        $sortOrder = $request->query->get('order', 'desc');
        $userId = $request->query->get('user');

        $queryBuilder = $movieRepository->getSortedQueryBuilder($sortBy, $sortOrder, $userId);

        $pagination = $paginator->paginate(
            $queryBuilder,
            $request->query->getInt('page', 1),
            10
        );

        $totalMovies = $pagination->getTotalItemCount();

        return $this->render('movie/index.html.twig', [
            'movies' => $pagination,
            'totalMovies' => $totalMovies,
            'currentSort' => $sortBy,
            'currentOrder' => $sortOrder,
            'totalShowedCount' => $totalMovies
        ]);
    }

    #[Route('/new', name: 'movie_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $movie = new Movie();

        $user = $this->getUser();

        if ($user) {
            $movie->setUser($user);
        } else {
            throw $this->createAccessDeniedException('You must be logged in to create a movie.');
        }

        $form = $this->createForm(MovieType::class, $movie);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($movie);
            $em->flush();

            return $this->redirectToRoute('movie_index');
        }

        return $this->render('movie/new.html.twig', [
            'movie' => $movie,
            'form' => $form,
        ]);
    }

    #[Route('/movies/{id}', name: 'movie_show', requirements: ['id' => Requirement::DIGITS], methods: ['GET'])]
    public function show(Movie $movie): Response
    {
        return $this->render('movie/show.html.twig', [
            'movie' => $movie,
        ]);
    }

    #[Route('/movies/{id}/edit', name: 'movie_edit', requirements: ['id' => Requirement::DIGITS], methods: ['GET', 'POST'])]
    public function edit(Request $request, Movie $movie, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(MovieType::class, $movie);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();

            return $this->redirectToRoute('movie_index');
        }

        return $this->render('movie/edit.html.twig', [
            'movie' => $movie,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/movies/{id}', name: 'movie_delete', requirements: ['id' => Requirement::DIGITS], methods: ['POST'])]
    public function delete(Request $request, Movie $movie, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete'.$movie->getId(), $request->request->get('_token'))) {
            $em->remove($movie);
            $em->flush();
        }

        return $this->redirectToRoute('movie_index');
    }
}
