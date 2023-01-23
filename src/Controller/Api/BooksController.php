<?php

namespace App\Controller\Api;

use Symfony\Component\Routing\Annotation\Route;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Annotations as OA;
use App\Repository\BookRepository;
use App\Entity\Book;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\Response;

class BooksController extends AbstractFOSRestController
{
    public const ID = 'id';
    public const NAME = 'name';
    public const RELEASE_DATE = 'releaseDate';
    public const CATALOG_ENTRY_DATE = 'catalogEntryDate';
    public const RATING = 'rating';
    public const GENRE = 'genre';
    public const AUTHOR = 'author';

    /**
     * @var BookRepository
     */
    private $bookRepository;

    public function __construct(
        BookRepository $bookRepository
    ) {
        $this->bookRepository = $bookRepository;
    }

    /**
     * @Route("/api/v1/books", name="api_get_all_books", methods={"GET"})
     * @OA\Response(
     *     response=200,
     *     description="Возвращает список книг",
     *     @OA\JsonContent(
     *        type="array",
     *        @OA\Items(ref=@Model(type=\App\Entity\Book::class))
     *     )
     * )
     */
    public function getAll(): JsonResponse
    {
        $books = $this->bookRepository->findAll();
        $booksArr = array();

        foreach ($books as $book) {
            $bookItem = $book->toArray();
            $booksArr[] = $bookItem;
        }

        return new JsonResponse($booksArr);
    }

    /**
     * @Route("/api/v1/books", name="api_add_book", methods={"POST"})
     * @OA\Response(
     *     response=200,
     *     description="Добавляет новую книгу",
     *     @OA\JsonContent(type="string")
     * )
     * @OA\Parameter(
     *     name="name",
     *     in="query",
     *     description="Название книги",
     *     @OA\Schema(type="string")
     * )
     * @OA\Parameter(
     *     name="releaseDate",
     *     in="query",
     *     description="Дата выпуска (формат: ГГГГ-ММ-ДД)",
     *     @OA\Schema(type="date")
     * )
     * @OA\Parameter(
     *     name="catalogEntryDate",
     *     in="query",
     *     description="Дата занесения в каталог (формат: ГГГГ-ММ-ДД)",
     *     @OA\Schema(type="date")
     * )
     * @OA\Parameter(
     *     name="rating",
     *     in="query",
     *     description="Рейтинг",
     *     @OA\Schema(type="integer")
     * )
     * @OA\Parameter(
     *     name="genre",
     *     in="query",
     *     description="Жанр книги",
     *     @OA\JsonContent(
     *          type="array",
     *          @OA\Items(type="integer")
     *     )
     * )
     * @OA\Parameter(
     *     name="author",
     *     in="query",
     *     description="Автор книги",
     *     @OA\JsonContent(
     *          type="array",
     *          @OA\Items(type="integer")
     *     )
     * )
     */
    public function add(Request $request): JsonResponse
    {
        $bookData = json_decode($request->getContent(), true);

        if (empty($bookData[self::NAME]) || empty($bookData[self::RELEASE_DATE]) || empty($bookData[self::CATALOG_ENTRY_DATE]) || empty($bookData[self::RATING])) {
            throw new NotFoundHttpException('Expecting mandatory parameters!');
        }

        $this->bookRepository->saveBook($bookData);

        return new JsonResponse(['status' => 'Book created!'], Response::HTTP_CREATED);
    }

    /**
     * @Route("/api/v1/books/{id}", name="api_get_one_book", methods={"GET"})
     * @OA\Response(
     *     response=200,
     *     description="Возвращает одну книгу",
     *     @Model(type=\App\Entity\Book::class)
     * )
     */
    public function getOne($id): JsonResponse
    {
        $book = $this->bookRepository->findOneBy(['id' => $id]);

        $bookData = $book->toArray();

        return new JsonResponse($bookData, Response::HTTP_OK);
    }

    /**
     * @Route("/api/v1/books/{id}", name="api_update_book", methods={"PUT"})
     * @OA\Response(
     *     response=200,
     *     description="Обновляет книгу",
     *     @OA\JsonContent(type="string")
     * )
     * @OA\Parameter(
     *     name="name",
     *     in="query",
     *     description="Название книги",
     *     @OA\Schema(type="string")
     * )
     * @OA\Parameter(
     *     name="releaseDate",
     *     in="query",
     *     description="Дата выпуска (формат: ГГГГ-ММ-ДД)",
     *     @OA\Schema(type="date")
     * )
     * @OA\Parameter(
     *     name="catalogEntryDate",
     *     in="query",
     *     description="Дата занесения в каталог (формат: ГГГГ-ММ-ДД)",
     *     @OA\Schema(type="date")
     * )
     * @OA\Parameter(
     *     name="rating",
     *     in="query",
     *     description="Рейтинг",
     *     @OA\Schema(type="integer")
     * )
     * @OA\Parameter(
     *     name="genre",
     *     in="query",
     *     description="Жанр книги",
     *     @OA\JsonContent(
     *          type="array",
     *          @OA\Items(type="integer")
     *     )
     * )
     * @OA\Parameter(
     *     name="author",
     *     in="query",
     *     description="Автор книги",
     *     @OA\JsonContent(
     *          type="array",
     *          @OA\Items(type="integer")
     *     )
     * )
     */
    public function update($id, Request $request): JsonResponse
    {
        $book = $this->bookRepository->findOneBy(['id' => $id]);

        $bookData = json_decode($request->getContent(), true);

        if (empty($bookData[self::NAME]) || empty($bookData[self::RELEASE_DATE]) || empty($bookData[self::CATALOG_ENTRY_DATE]) || empty($bookData[self::RATING])) {
            throw new NotFoundHttpException('Expecting mandatory parameters!');
        }

        $this->bookRepository->saveBook($bookData, $book);

        $this->bookRepository->add($book, true);

        return new JsonResponse($book->toArray(), Response::HTTP_OK);
    }

    /**
     * @Route("/api/v1/books/{id}", name="api_delete_book", methods={"DELETE"})
     * @OA\Response(
     *     response=200,
     *     description="Удаляет одну книгу",
     *     @OA\JsonContent(type="string")
     * )
     */
    public function delete($id): JsonResponse
    {
        $book = $this->bookRepository->findOneBy(['id' => $id]);

        $this->bookRepository->remove($book, true);

        return new JsonResponse(['status' => 'Book deleted'], Response::HTTP_OK);
    }
}
