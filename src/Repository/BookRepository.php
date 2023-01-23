<?php

namespace App\Repository;

use App\Entity\Book;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Filter\FilterInterface;
use App\Repository\GenreRepository;
use App\Repository\AuthorRepository;
use Doctrine\Common\Collections\ArrayCollection;
use App\Entity\Author;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\PersistentCollection;

/**
 * @extends ServiceEntityRepository<Book>
 *
 * @method Book|null find($id, $lockMode = null, $lockVersion = null)
 * @method Book|null findOneBy(array $criteria, array $orderBy = null)
 * @method Book[]    findAll()
 * @method Book[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BookRepository extends ServiceEntityRepository
{
    private const RECOMMENDED_BOOKS_LIMIT= 5;
    private const LATEST_BOOKS_LIMIT= 10;

    /**
     * @var \App\Repository\GenreRepository
     */
    private $genreRepository;

    /**
     * @var \App\Repository\AuthorRepository
     */
    private $authorRepository;

    public function __construct(
        ManagerRegistry $registry,
        GenreRepository $genreRepository,
        AuthorRepository $authorRepository
    ) {
        parent::__construct($registry, Book::class);
        $this->genreRepository = $genreRepository;
        $this->authorRepository = $authorRepository;
    }

    public function add(Book $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Book $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @param array $bookData
     * @param Book|null $book
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function saveBook(array $bookData, Book $book = null): void
    {
        $newBook = $book ?? (new Book());

        $newBook->setName($bookData['name']);

        $releaseDate = date_create('@'. strtotime($bookData['releaseDate']));
        $newBook->setReleaseDate($releaseDate);

        $catalogEntryDate = date_create('@'. strtotime($bookData['catalogEntryDate']));
        $newBook->setCatalogEntryDate($catalogEntryDate);

        $newBook->setRating($bookData['rating']);

        $genres = $this->genreRepository->findGenresByIds($bookData['genre']);
        $genresCollection = new ArrayCollection();

        foreach ($genres as $genre) {
            $genresCollection->add($genre);
        }

        $newBook->setGenre($genresCollection);

        $authors = $this->authorRepository->findAuthorsByIds($bookData['author']);
        $authorsCollection = new ArrayCollection();

        foreach ($authors as $author) {
            $authorsCollection->add($author);
        }

        $newBook->setAuthor($authorsCollection);

        $newBook->setLikes(0);
        $newBook->setDislikes(0);

        $bookData['avatar'] = $bookData['avatar'] ?? '';
        $newBook->setAvatar($bookData['avatar']);

        if (!$book) {
            $this->getEntityManager()->persist($newBook);
        }

        $this->getEntityManager()->flush();
    }

    /**
     * @return Book[] Returns an array of Book objects
     */
    public function findByFilter(FilterInterface $filter): array
    {
        $query = $this->createQueryBuilder('b');

        $genre = $filter->getGenre();
        if ($genre) {
            $query = $query
                ->andWhere('g.id = :genre')
                ->setParameter('genre', $genre)
                ->leftJoin('b.genre', 'g')
            ;
        }

        $author = $filter->getAuthor();
        if ($author) {
            $query = $query
                ->andWhere('a.id = :author')
                ->setParameter('author', $author)
                ->leftJoin('b.author', 'a')
            ;
        }

        $startReleaseDate = $filter->getStartReleaseDate();
        if ($startReleaseDate) {
            $query = $query
                ->andWhere('b.releaseDate >= :startReleaseDate')
                ->setParameter('startReleaseDate', $startReleaseDate)
            ;
        }

        $endReleaseDate = $filter->getEndReleaseDate();
        if ($endReleaseDate) {
            $query = $query
                ->andWhere('b.releaseDate <= :endReleaseDate')
                ->setParameter('endReleaseDate', $endReleaseDate)
            ;
        }

        return $query->orderBy('b.id', 'ASC')->getQuery()->getResult();
    }

    /**
     * @param string $searchTerm
     * @return array
     */
    public function search(string $searchTerm): array
    {
        $query = $this->createQueryBuilder('b');

        $query
            ->andWhere('b.name LIKE :searchTerm
                OR a.name LIKE :searchTerm
                OR g.name LIKE :searchTerm'
            )
            ->leftJoin('b.author', 'a')
            ->leftJoin('b.genre', 'g')
            ->setParameter('searchTerm', '%' . $searchTerm . '%')
            ;

        return $query->orderBy('b.id', 'ASC')->getQuery()->getResult();
    }

    /**
     * @return array
     */
    public function getRecommendedBooks(Book $book): array
    {
        $recommendedBooks = [];

        $query = $this->createQueryBuilder('b');

        $currentBookId = $book->getId();
        $genres = $this->collectionObjectToArray($book->getGenre());
        $genreIds = array_keys($genres);
        $query = $query
            ->andWhere('b.id <> :id')
            ->setParameter('id', $currentBookId)
            ->andWhere('g.id in (:genreIds)')
            ->setParameter('genreIds', $genreIds)
            ->leftJoin('b.genre', 'g')
        ;

        $definiteGenreBookItems = $query->orderBy('b.id', 'ASC')->setMaxResults(self::RECOMMENDED_BOOKS_LIMIT)->getQuery()->getResult();

        foreach ($definiteGenreBookItems as $definiteGenreBookItem) {
            $recommendedBooks[$definiteGenreBookItem->getId()] = $definiteGenreBookItem;
        }

        if (count($recommendedBooks) > 4) {
            return $recommendedBooks;
        }

        $query = $this->createQueryBuilder('b');
        $authors = $this->collectionObjectToArray($book->getAuthor());
        $authorIds = array_keys($authors);
        $query = $query
            ->andWhere('b.id <> :id')
            ->setParameter('id', $currentBookId)
            ->andWhere('a.id in (:authorIds)')
            ->setParameter('authorIds', $authorIds)
            ->leftJoin('b.author', 'a')
        ;

        $recommendedBooksCount = count($recommendedBooks);
        $definiteAuthorBookItems = $query->orderBy('b.id', 'ASC')->setMaxResults(self::RECOMMENDED_BOOKS_LIMIT - $recommendedBooksCount)->getQuery()->getResult();

        foreach ($definiteAuthorBookItems as $definiteAuthorBookItem) {
            $recommendedBooks[$definiteAuthorBookItem->getId()] = $definiteAuthorBookItem;
        }

        if (count($recommendedBooks) > 4) {
            return $recommendedBooks;
        }

        $query = $this->createQueryBuilder('b');
        $query = $query
            ->andWhere('b.id <> :id')
            ->setParameter('id', $currentBookId)
        ;

        $recommendedBooksCount = count($recommendedBooks);
        $randomBookItems = $query->orderBy('b.id', 'ASC')->setMaxResults(self::RECOMMENDED_BOOKS_LIMIT - $recommendedBooksCount)->getQuery()->getResult();

        foreach ($randomBookItems as $randomBookItem) {
            $recommendedBooks[$randomBookItem->getId()] = $randomBookItem;
        }

        return $recommendedBooks;
    }

    /**
     * @param object $collection
     * @return array
     */
    private function collectionObjectToArray(object $collection): array
    {
        $items = [];

        foreach ($collection as $item) {
            $items[$item->getId()] = $item;
        }

        return $items;
    }

    public function getLatestBooks()
    {
        $query = $this->createQueryBuilder('b');

        $latestBooks = $query->orderBy('b.catalogEntryDate', 'DESC')->setMaxResults(self::LATEST_BOOKS_LIMIT)->getQuery()->getResult();

        return $latestBooks;
    }

    /**
     * @param array $books
     * @return array
     */
    public function getGenresCatalog(array $books): array
    {
        $genresCatalog = [];

        foreach ($books as $book) {
            $genres = $book->getGenre();
            $genresCatalog[$book->getId()] = [];

            foreach ($genres as $genre) {
                $genresCatalog[$book->getId()][] = $genre;
            }
        }

        return $genresCatalog;
    }

    public function getAuthorsCatalog(array $books): array
    {
        $authorsCatalog = [];

        foreach ($books as $book) {
            $authors = $book->getAuthor();
            $authorsCatalog[$book->getId()] = [];

            foreach ($authors as $author) {
                $authorsCatalog[$book->getId()][] = $author;
            }
        }

        return $authorsCatalog;
    }

//    /**
//     * @return Book[] Returns an array of Book objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('b')
//            ->andWhere('b.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('b.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Book
//    {
//        return $this->createQueryBuilder('b')
//            ->andWhere('b.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
