<?php

namespace App\Controller\Api;

use Symfony\Component\Routing\Annotation\Route;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Annotations as OA;
use App\Repository\GenreRepository;
use App\Entity\Genre;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\Response;

class GenresController extends AbstractFOSRestController
{
    public const ID = 'id';
    public const NAME = 'name';

    /**
     * @var GenreRepository
     */
    private $genreRepository;

    public function __construct(
        GenreRepository $genreRepository
    ) {
        $this->genreRepository = $genreRepository;
    }

    /**
     * @Route("/api/v1/genres", name="api_get_all_genres", methods={"GET"})
     * @OA\Response(
     *     response=200,
     *     description="Возвращает список жанров",
     *     @OA\JsonContent(
     *        type="array",
     *        @OA\Items(ref=@Model(type=\App\Entity\Genre::class))
     *     )
     * )
     */
    public function getAll(): JsonResponse
    {
        $genres = $this->genreRepository->findAll();
        $genresArr = array();

        foreach ($genres as $genre) {
            $genreItem = $genre->toArray();
            $genresArr[] = $genreItem;
        }

        return new JsonResponse($genresArr);
    }

    /**
     * @Route("/api/v1/genres", name="api_add_genre", methods={"POST"})
     * @OA\Response(
     *     response=200,
     *     description="Добавляет новый жанр",
     *     @OA\JsonContent(type="string")
     * )
     * @OA\Parameter(
     *     name="name",
     *     in="query",
     *     description="Название жанра",
     *     @OA\Schema(type="string")
     * )
     */
    public function add(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $name = $data[self::NAME];

        if (empty($name)) {
            throw new NotFoundHttpException('Expecting mandatory parameters!');
        }

        $genreData = [
            self::NAME => $name,
        ];

        $this->genreRepository->saveGenre($genreData);

        return new JsonResponse(['status' => 'Genre created!'], Response::HTTP_CREATED);
    }

    /**
     * @Route("/api/v1/genres/{id}", name="api_get_one_genre", methods={"GET"})
     * @OA\Response(
     *     response=200,
     *     description="Возвращает один жанр",
     *     @Model(type=\App\Entity\Genre::class)
     * )
     */
    public function getOne($id): JsonResponse
    {
        $genre = $this->genreRepository->findOneBy(['id' => $id]);

        $genreData = $genre->toArray();

        return new JsonResponse($genreData, Response::HTTP_OK);
    }

    /**
     * @Route("/api/v1/genres/{id}", name="api_update_genre", methods={"PUT"})
     * @OA\Response(
     *     response=200,
     *     description="Обновляет жанр",
     *     @OA\JsonContent(type="string")
     * )
     * @OA\Parameter(
     *     name="name",
     *     in="query",
     *     description="Название жанра",
     *     @OA\Schema(type="string")
     * )
     */
    public function update($id, Request $request): JsonResponse
    {
        $genre = $this->genreRepository->findOneBy(['id' => $id]);

        $genreData = json_decode($request->getContent(), true);

        $this->genreRepository->saveGenre($genreData, $genre);

        return new JsonResponse($genre->toArray(), Response::HTTP_OK);
    }

    /**
     * @Route("/api/v1/genres/{id}", name="api_delete_genre", methods={"DELETE"})
     * @OA\Response(
     *     response=200,
     *     description="Удаляет один жанр",
     *     @OA\JsonContent(type="string")
     * )
     */
    public function delete($id): JsonResponse
    {
        $genre = $this->genreRepository->findOneBy(['id' => $id]);

        $this->genreRepository->remove($genre, true);

        return new JsonResponse(['status' => 'Genre deleted'], Response::HTTP_OK);
    }
}
