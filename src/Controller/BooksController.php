<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Book;
use App\Form\BookType;
use App\Repository\BookRepository;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\AuthorRepository;
use App\Repository\GenreRepository;
use App\Form\SearchFormType;
use App\Form\OmniboxSearchFormType;
use Knp\Component\Pager\PaginatorInterface;
use App\Filter\Filter;
use Symfony\Component\Security\Core\Security;
use App\Repository\UserRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Common\Collections\ArrayCollection;

class BooksController extends AbstractController
{
    public const NAME = 'name';
    public const RELEASE_DATE = 'releaseDate';
    public const CATALOG_ENTRY_DATE = 'catalogEntryDate';
    public const RATING = 'rating';
    public const GENRE = 'genre';
    public const AUTHOR = 'author';
    public const AVATAR = 'avatar';

    /**
     * @var BookRepository
     */
    private $bookRepository;

    /**
     * @var AuthorRepository
     */
    private $authorRepository;

    /**
     * @var GenreRepository
     */
    private $genreRepository;

    /**
     * @var UserRepository
     */
    private $userRepository;

    /**
     * @var ManagerRegistry
     */
    private $managerRegistry;

    /**
     * @var Security
     */
    private $security;

    /**
     * @var PaginatorInterface
     */
    private $paginator;

    public function __construct(
        BookRepository $bookRepository,
        AuthorRepository $authorRepository,
        GenreRepository $genreRepository,
        UserRepository $userRepository,
        ManagerRegistry $managerRegistry,
        Security $security,
        PaginatorInterface $paginator
    ) {
        $this->bookRepository = $bookRepository;
        $this->authorRepository = $authorRepository;
        $this->genreRepository = $genreRepository;
        $this->userRepository = $userRepository;
        $this->managerRegistry = $managerRegistry;
        $this->security = $security;
        $this->paginator = $paginator;
    }

    /**
     * @Route("/", name="get_all_books")
     */
    public function getAll(Request $request): Response
    {
        $searchForm = $this->createForm(SearchFormType::class);
        $searchForm->handleRequest($request);

        $omniboxSearchForm = $this->createForm(OmniboxSearchFormType::class);
        $omniboxSearchForm->handleRequest($request);

        if ($searchForm->isSubmitted() && $searchForm->isValid()) {
            $formData = $searchForm->getData();

            $filter = new Filter();

            $dateStart = $formData['date_start'];
            if ($dateStart) {
                $convertedStartReleaseDate = date('Y-d-m', strtotime($dateStart));
                $filter->setStartReleaseDate($convertedStartReleaseDate);
            }

            $dateEnd = $formData['date_end'];
            if ($dateEnd) {
                $convertedEndReleaseDate = date('Y-d-m', strtotime($dateEnd));
                $filter->setEndReleaseDate($convertedEndReleaseDate);
            }

            $filter->setGenre($formData['genre']);
            $filter->setAuthor($formData['author']);

            $books = $this->bookRepository->findByFilter($filter);
        } elseif ($omniboxSearchForm->isSubmitted() && $omniboxSearchForm->isValid()) {
            $formData = $omniboxSearchForm->getData();
            $books = $this->bookRepository->search($formData['search']);
        } else {
            $books = $this->bookRepository->findAll();
        }

        $genresCatalog = $this->bookRepository->getGenresCatalog($books);
        $authorsCatalog = $this->bookRepository->getAuthorsCatalog($books);

        $pagination = $this->paginator->paginate($books, $request->query->getInt('page', 1), 5);

        $templateData = [
            'pagination' => $pagination,
            'genresCatalog' => $genresCatalog,
            'authorsCatalog' => $authorsCatalog,
        ];

        $favoriteBookItems = [];
        $authorizedUser = $this->security->getUser();

        if ($authorizedUser) {
            $user = $this->userRepository->findByUsername($this->security->getUser()->getUserIdentifier());
            $favoriteBookItems = $user->getBook();
            $templateData['authorizedUser'] = $authorizedUser;
        }

        $latestBooks = $this->bookRepository->getLatestBooks();

        if ($request->headers->get('x-pjax')) {
            return $this->render('books/book_grid.html.twig', $templateData);
        }

        return $this->render('books/index.html.twig', array_merge($templateData, [
            'searchForm' => $searchForm->createView(),
            'omniboxSearchForm' => $omniboxSearchForm->createView(),
            'favoriteBooks' => $favoriteBookItems,
            'latestBooks' => $latestBooks,
        ]));
    }

    /**
     * @Route("/books/new", name="add_book")
     */
    public function add(Request $request): Response
    {
        $book = new Book();
        $form = $this->createForm(BookType::class, $book);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            $genres = [];
            foreach ($data->getGenre() as $genre) {
                $genres[] = $genre->getId();
            }

            $authors = [];
            foreach ($data->getAuthor() as $author) {
                $authors[] = $author->getId();
            }

            $bookData = [
                self::NAME => $data->getName(),
                self::RELEASE_DATE => $data->getReleaseDate()->format('Y-m-d'),
                self::CATALOG_ENTRY_DATE => $data->getCatalogEntryDate()->format('Y-m-d'),
                self::RATING => $data->getRating(),
                self::GENRE => $genres,
                self::AUTHOR => $authors,
                self::AVATAR => $data->getAvatar(),
            ];

            $bookData = $this->saveAvatar($bookData, $request, $data->getAvatar());

            $this->bookRepository->saveBook($bookData);

            return $this->redirectToRoute('get_all_books');
        }

        $templateData = [
            'form' => $form->createView(),
            'existedAvatar' => $book->getAvatar(),
        ];

        $authorizedUser = $this->security->getUser();

        if ($authorizedUser) {
            $templateData['authorizedUser'] = $authorizedUser;
        }

        return $this->render('books/new.html.twig', $templateData);
    }

    /**
     * @Route("/books/{id}", name="get_one_book")
     */
    public function getOne(Book $book)
    {
        $recommendedBooks = $this->bookRepository->getRecommendedBooks($book);
        $book = $book->toArray();

        return $this->render('books/show.html.twig', [
            'book' => $book,
            'recommendedBooks' => $recommendedBooks,
        ]);
    }

    /**
     * @Route("/books/{id}/edit", name="update_book")
     */
    public function update(Book $book, Request $request): Response
    {
        $existedAvatar = $book->getAvatar();
        $form = $this->createForm(BookType::class, $book);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $bookData = $request->request->get('book');

            $bookData = $this->saveAvatar($bookData, $request, $existedAvatar);

            $this->bookRepository->saveBook($bookData, $book);

            return $this->redirectToRoute('get_one_book', [
                'id' => $book->getId()
            ]);
        }

        $templateData = [
            'form' => $form->createView(),
            'existedAvatar' => $book->getAvatar()
        ];

        $authorizedUser = $this->security->getUser();

        if ($authorizedUser) {
            $templateData['authorizedUser'] = $authorizedUser;
        }

        return $this->render('books/new.html.twig', $templateData);
    }

    /**
     * @param $bookData
     * @param Request $request
     * @param string $avatar
     * @return array
     */
    private function saveAvatar($bookData, Request $request, string $avatar): array
    {
        $avatarPath = explode('/', $avatar);
        $avatarName = array_pop($avatarPath);

        if ($avatarName === Book::NULL_IMAGE) {
            $bookData['avatar'] = '';
        } else {
            $bookData['avatar'] = $avatarName;
        }

        $avatarData = $request->files->get('book')['avatar'];
        if ($avatarData && $avatarData->getError() == UPLOAD_ERR_OK) {
            $name = $avatarData->getClientOriginalName();
            if (!file_exists(Book::IMAGES_DIR)) {
                mkdir(Book::IMAGES_DIR, 0777, true);
            }

            $path = $this->getParameter('kernel.project_dir') . Book::PUBLIC_DIR . Book::UPLOAD_IMAGES_DIR;
            $avatarData->move($path, $name);
            $bookData['avatar'] = $name;
        }

        return $bookData;
    }

    /**
     * @Route("/books/{id}/delete", name="delete_book")
     */
    public function delete($id): Response
    {
        $book = $this->bookRepository->findOneBy(['id' => $id]);
        $this->bookRepository->remove($book, true);

        return $this->redirectToRoute('get_all_books');
    }

    /**
     * @Route("/books/{id}/favorite", name="favorite_book")
     */
    public function favorite($id, Request $request): Response
    {
        $book = $this->bookRepository->findOneBy(['id' => $id]);

        $user = $this->userRepository->findByUsername($this->security->getUser()->getUserIdentifier());
        $usersCollection = new ArrayCollection();
        $usersCollection->add($user);

        $book->setUser($usersCollection);

        $em = $this->managerRegistry->getManager();
        $em->persist($book);
        $em->flush();

        return $this->redirectToRoute('get_all_books');
    }
}
