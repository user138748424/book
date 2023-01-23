<?php

namespace App\Controller\Api;

use Symfony\Component\Routing\Annotation\Route;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Annotations as OA;
use App\Repository\AuthorRepository;
use App\Entity\Author;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\Response;

class AuthorsController extends AbstractFOSRestController
{
    public const ID = 'id';
    public const NAME = 'name';
    public const BORN_DATE = 'bornDate';
    public const GENDER = 'gender';

    /**
     * @var AuthorRepository
     */
    private $authorRepository;

    public function __construct(
        AuthorRepository $authorRepository
    ) {
        $this->authorRepository = $authorRepository;
    }

    /**
     * @Route("/api/v1/authors", name="api_get_all_authors", methods={"GET"})
     * @OA\Response(
     *     response=200,
     *     description="Возвращает список авторов",
     *     @OA\JsonContent(
     *        type="array",
     *        @OA\Items(ref=@Model(type=\App\Entity\Author::class))
     *     )
     * )
     */
    public function getAll(): JsonResponse
    {
        $authors = $this->authorRepository->findAll();
        $authorsArr = array();

        foreach ($authors as $author) {
            $authorItem = $author->toArray();
            $authorsArr[] = $authorItem;
        }

        return new JsonResponse($authorsArr);
    }

    /**
     * @Route("/api/v1/authors", name="api_add_author", methods={"POST"})
     * @OA\Response(
     *     response=200,
     *     description="Добавляет нового автора",
     *     @OA\JsonContent(type="string")
     * )
     * @OA\Parameter(
     *     name="name",
     *     in="query",
     *     description="Название автора",
     *     @OA\Schema(type="string")
     * )
     * @OA\Parameter(
     *     name="bornDate",
     *     in="query",
     *     description="Дата рождения (формат: ГГГГ-ММ-ДД)",
     *     @OA\Schema(type="date")
     * )
     * @OA\Parameter(
     *     name="gender",
     *     in="query",
     *     description="Пол",
     *     @OA\Schema(type="string")
     * )
     */
    public function add(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $name = $data[self::NAME];
        $bornDate = $data[self::BORN_DATE];
        $gender = $data[self::GENDER];

        if (empty($name) || empty($bornDate) || empty($gender)) {
            throw new NotFoundHttpException('Expecting mandatory parameters!');
        }

        $authorData = [
            self::NAME => $name,
            self::BORN_DATE => $bornDate,
            self::GENDER => $gender,
        ];

        $this->authorRepository->saveAuthor($authorData);

        return new JsonResponse(['status' => 'Author created!'], Response::HTTP_CREATED);
    }

    /**
     * @Route("/api/v1/authors/{id}", name="api_get_one_author", methods={"GET"})
     * @OA\Response(
     *     response=200,
     *     description="Возвращает одного автора",
     *     @Model(type=\App\Entity\Author::class)
     * )
     */
    public function getOne($id): JsonResponse
    {
        $author = $this->authorRepository->findOneBy(['id' => $id]);

        $authorData = $author->toArray();

        return new JsonResponse($authorData, Response::HTTP_OK);
    }

    /**
     * @Route("/api/v1/authors/{id}", name="api_update_author", methods={"PUT"})
     * @OA\Response(
     *     response=200,
     *     description="Обновляет автора",
     *     @OA\JsonContent(type="string")
     * )
     * @OA\Parameter(
     *     name="name",
     *     in="query",
     *     description="Имя автора",
     *     @OA\Schema(type="string")
     * )
     * @OA\Parameter(
     *     name="bornDate",
     *     in="query",
     *     description="Дата рождения (формат: ГГГГ-ММ-ДД)",
     *     @OA\Schema(type="date")
     * )
     * @OA\Parameter(
     *     name="gender",
     *     in="query",
     *     description="Пол",
     *     @OA\Schema(type="string")
     * )
     */
    public function update($id, Request $request): JsonResponse
    {
        $author = $this->authorRepository->findOneBy(['id' => $id]);

        $authorData = json_decode($request->getContent(), true);

        $this->authorRepository->saveAuthor($authorData, $author);

        return new JsonResponse($author->toArray(), Response::HTTP_OK);
    }

    /**
     * @Route("/api/v1/authors/{id}", name="api_delete_author", methods={"DELETE"})
     * @OA\Response(
     *     response=200,
     *     description="Удаляет одного автора",
     *     @OA\JsonContent(type="string")
     * )
     */
    public function delete($id): JsonResponse
    {
        $author = $this->authorRepository->findOneBy(['id' => $id]);

        $this->authorRepository->remove($author, true);

        return new JsonResponse(['status' => 'Author deleted'], Response::HTTP_OK);
    }
}
