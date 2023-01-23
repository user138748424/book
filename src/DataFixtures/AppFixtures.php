<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use App\Entity\Book;
use App\Entity\Genre;
use App\Entity\Author;

class AppFixtures extends Fixture
{
    private $faker;

    public function __construct()
    {
        $this->faker = Factory::create();
    }

    public function load(ObjectManager $manager): void
    {
        $this->loadGenres($manager);
        $this->loadAuthors($manager);
        $this->loadBooks($manager);
    }

    public function loadGenres(ObjectManager $manager): void
    {
        for ($i = 1; $i < 20; $i++) {
            $genre = new Genre();
            $genre->setName($this->faker->text(20));

            $manager->persist($genre);
        }
        $manager->flush();
    }

    public function loadAuthors(ObjectManager $manager): void
    {
        for ($i = 1; $i < 20; $i++) {
            $author = new Author();
            $author->setName($this->faker->text(20));
            $author->setBornDate($this->faker->dateTime);
            $author->setGender($this->faker->text(20));

            $manager->persist($author);
        }
        $manager->flush();
    }

    public function loadBooks(ObjectManager $manager): void
    {
        $genres = $manager->getRepository(Genre::class)->findAll();
        $authors = $manager->getRepository(Author::class)->findAll();

        for ($i = 1; $i < 20; $i++) {
            $book = new Book();
            $book->setName($this->faker->text(20));
            $book->setCatalogEntryDate($this->faker->dateTime);
            $book->setReleaseDate($this->faker->dateTime);
            $book->setRating($this->faker->randomFloat(null, 0, 10));

            $book->setGenre([$this->faker->randomElement($genres)]);

            $book->setAuthor([$this->faker->randomElement($authors)]);

            $manager->persist($book);
        }
        $manager->flush();
    }
}
