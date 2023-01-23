<?php

namespace App\Repository;

use App\Entity\Genre;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Filter\FilterInterface;

/**
 * @extends ServiceEntityRepository<Genre>
 *
 * @method Genre|null find($id, $lockMode = null, $lockVersion = null)
 * @method Genre|null findOneBy(array $criteria, array $orderBy = null)
 * @method Genre[]    findAll()
 * @method Genre[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class GenreRepository extends ServiceEntityRepository
{
    private const BOOK_GENRE_TABLE = 'book_genre';

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Genre::class);
    }

    public function add(Genre $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Genre $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @param array $genreData
     * @param Genre|null $genre
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function saveGenre(array $genreData, Genre $genre = null)
    {
        $newGenre = $genre ?? (new Genre());

        $newGenre->setName($genreData['name']);

        if (!$genre) {
            $this->getEntityManager()->persist($newGenre);
        }

        $this->getEntityManager()->flush();
    }

    /**
     * @return Genre[] Returns an array of Genre objects
     */
    public function findByFilter(FilterInterface $filter): array
    {
        $query = $this->createQueryBuilder('g');

        $genreId = $filter->getGenre();
        if ($genreId) {
            $query = $query
                ->andWhere('g.id = :genreId')
                ->setParameter('genreId', $genreId)
            ;
        }

        return $query->orderBy('g.id', 'ASC')->getQuery()->getResult();
    }

    /**
     * @param array $genresIds
     * @return Genre[] Returns an array of Genre objects
     */
    public function findGenresByIds(array $genresIds): array
    {
        return $this->createQueryBuilder('g')
            ->andWhere('g.id in (:genresIds)')
            ->setParameter('genresIds', $genresIds)
            ->orderBy('g.id', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

//    /**
//     * @return Genre[] Returns an array of Genre objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('g')
//            ->andWhere('g.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('g.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Genre
//    {
//        return $this->createQueryBuilder('g')
//            ->andWhere('g.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
