<?php

namespace App\Controller;

use App\Filter\Filter;
use App\Form\SearchFormType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Author;
use App\Form\AuthorType;
use App\Repository\AuthorRepository;
use Symfony\Component\HttpFoundation\Request;
use Knp\Component\Pager\PaginatorInterface;

class AuthorsController extends AbstractController
{
    /**
     * @var AuthorRepository
     */
    private $authorRepository;

    /**
     * @var PaginatorInterface
     */
    private $paginator;

    public function __construct(
        AuthorRepository $authorRepository,
        PaginatorInterface $paginator
    ) {
        $this->authorRepository = $authorRepository;
        $this->paginator = $paginator;
    }

    /**
     * @Route("/authors", name="get_all_authors")
     */
    public function getAll(Request $request): Response
    {
        $searchForm = $this->createForm(SearchFormType::class);
        $searchForm->handleRequest($request);
        if ($searchForm->isSubmitted() && $searchForm->isValid()) {
            $formData = $searchForm->getData();

            $filter = new Filter();

            $filter->setAuthor($formData['author']);

            $genres = $this->authorRepository->findByFilter($filter);
        } else {
            $genres = $this->authorRepository->findAll();
        }

        $pagination = $this->paginator->paginate($genres, $request->query->getInt('page', 1), 5);

        $templateData = ['pagination' => $pagination];

        if ($request->headers->get('x-pjax')) {
            return $this->render('authors/author_grid.html.twig', $templateData);
        }

        return $this->render('authors/index.html.twig', array_merge($templateData, [
            'searchForm' => $searchForm->createView()
        ]));
    }

    /**
     * @Route("/authors/new", name="add_author")
     */
    public function add(Request $request): Response
    {
        $author = new Author();
        $form = $this->createForm(AuthorType::class, $author);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            $this->authorRepository->saveAuthor($data->toArray());

            return $this->redirectToRoute('get_all_authors');
        }
        return $this->render('authors/new.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/authors/{id}", name="get_one_author")
     */
    public function getOne(Author $author)
    {
        $author = $author->toArray();

        return $this->render('authors/show.html.twig', [
            'author' => $author
        ]);
    }

    /**
     * @Route("/authors/{id}/edit", name="update_author")
     */
    public function update(Author $author, Request $request): Response
    {
        $form = $this->createForm(AuthorType::class, $author);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $authorData = $request->request->get('author');
            $this->authorRepository->saveAuthor($authorData, $author);

            return $this->redirectToRoute('get_one_author', [
                'id' => $author->getId()
            ]);
        }

        return $this->render('authors/new.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/authors/{id}/delete", name="delete_author")
     */
    public function delete($id): Response
    {
        $author = $this->authorRepository->findOneBy(['id' => $id]);
        $this->authorRepository->remove($author, true);

        return $this->redirectToRoute('get_all_authors');
    }
}
