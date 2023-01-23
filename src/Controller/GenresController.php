<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Genre;
use App\Form\GenreType;
use App\Repository\GenreRepository;
use Symfony\Component\HttpFoundation\Request;
use App\Filter\Filter;
use App\Form\SearchFormType;
use Knp\Component\Pager\PaginatorInterface;

class GenresController extends AbstractController
{
    /**
     * @var GenreRepository
     */
    private $genreRepository;

    /**
     * @var PaginatorInterface
     */
    private $paginator;

    public function __construct(
        GenreRepository $genreRepository,
        PaginatorInterface $paginator
    ) {
        $this->genreRepository = $genreRepository;
        $this->paginator = $paginator;
    }

    /**
     * @Route("/genres", name="get_all_genres")
     */
    public function getAll(Request $request): Response
    {
        $searchForm = $this->createForm(SearchFormType::class);
        $searchForm->handleRequest($request);
        if ($searchForm->isSubmitted() && $searchForm->isValid()) {
            $formData = $searchForm->getData();

            $filter = new Filter();

            $filter->setGenre($formData['genre']);

            $genres = $this->genreRepository->findByFilter($filter);
        } else {
            $genres = $this->genreRepository->findAll();
        }

        $pagination = $this->paginator->paginate($genres, $request->query->getInt('page', 1), 5);

        $templateData = ['pagination' => $pagination];

        if ($request->headers->get('x-pjax')) {
            return $this->render('genres/genre_grid.html.twig', $templateData);
        }

        return $this->render('genres/index.html.twig', array_merge($templateData, [
            'searchForm' => $searchForm->createView()
        ]));
    }

    /**
     * @Route("/genres/new", name="add_genre")
     */
    public function add(Request $request): Response
    {
        $genre = new Genre();
        $form = $this->createForm(GenreType::class, $genre);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            $this->genreRepository->saveGenre($data->toArray());

            return $this->redirectToRoute('get_all_genres');
        }
        return $this->render('genres/new.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/genres/{id}", name="get_one_genre")
     */
    public function getOne(Genre $genre)
    {
        $genre = $genre->toArray();

        return $this->render('genres/show.html.twig', [
            'genre' => $genre
        ]);
    }

    /**
     * @Route("/genres/{id}/edit", name="update_genre")
     */
    public function update(Genre $genre, Request $request): Response
    {
        $form = $this->createForm(GenreType::class, $genre);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $genreData = $request->request->get('genre');
            $this->genreRepository->saveGenre($genreData, $genre);

            return $this->redirectToRoute('get_one_genre', [
                'id' => $genre->getId()
            ]);
        }

        return $this->render('genres/new.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/genres/{id}/delete", name="delete_genre")
     */
    public function delete($id): Response
    {
        $genre = $this->genreRepository->findOneBy(['id' => $id]);
        $this->genreRepository->remove($genre, true);

        return $this->redirectToRoute('get_all_genres');
    }
}
