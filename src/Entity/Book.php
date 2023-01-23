<?php

namespace App\Entity;

use App\Repository\BookRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=BookRepository::class)
 */
class Book
{
    public const IMAGES_DIR = __DIR__ . '/../../assets/images/';
    public const NULL_IMAGE = 'null-image.jpeg';
    public const NULL_IMAGE_DIR = '/images/';
    public const UPLOAD_IMAGES_DIR = '/uploads/';
    public const PUBLIC_DIR = '/public';

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", nullable=false)
     * @Assert\Length(min=1, max=255)
     */
    private $name;

    /**
     * @ORM\Column(type="datetime")
     */
    private $releaseDate;

    /**
     * @ORM\Column(type="datetime")
     */
    private $catalogEntryDate;

    /**
     * @ORM\Column(type="float")
     * @Assert\Range(min = 0, max = 10, minMessage = "You must be at least 0 rating", maxMessage = "Your maximum 10 rating")
     */
    private $rating;

    /**
     * @ORM\Column(type="integer")
     */
    private $likes;

    /**
     * @ORM\Column(type="integer")
     */
    private $dislikes;

    /**
     * @var object
     * @ORM\ManyToMany(targetEntity="App\Entity\Genre", cascade={"persist"})
     * @ORM\JoinTable(name="book_genre")
     * @ORM\JoinColumn(referencedColumnName="id", nullable=false)
     */
    private $genre;

    /**
     * @var object
     * @ORM\ManyToMany(targetEntity="App\Entity\Author", cascade={"persist"})
     * @ORM\JoinTable(name="book_author")
     * @ORM\JoinColumn(referencedColumnName="id", nullable=false)
     */
    private $author;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $avatar;

    /**
     * @var object
     * @ORM\ManyToMany(targetEntity="App\Entity\User", cascade={"persist"}, inversedBy="book")
     * @ORM\JoinTable(name="book_user")
     * @ORM\JoinColumn(referencedColumnName="id", nullable=false)
     */
    private $user;

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param string|null $name
     */
    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return \DateTimeInterface
     */
    public function getReleaseDate(): ?\DateTimeInterface
    {
        return $this->releaseDate;
    }

    /**
     * @param \DateTimeInterface $releaseDate
     */
    public function setReleaseDate(\DateTimeInterface $releaseDate): void
    {
        $this->releaseDate = $releaseDate;
    }

    /**
     * @return \DateTimeInterface
     */
    public function getCatalogEntryDate(): ?\DateTimeInterface
    {
        return $this->catalogEntryDate;
    }

    /**
     * @param \DateTimeInterface $catalogEntryDate
     */
    public function setCatalogEntryDate(\DateTimeInterface $catalogEntryDate): void
    {
        $this->catalogEntryDate = $catalogEntryDate;
    }

    /**
     * @return float|null
     */
    public function getRating(): ?float
    {
        return $this->rating;
    }

    /**
     * @param float|null $rating
     */
    public function setRating(?float $rating): void
    {
        $this->rating = $rating;
    }

    /**
     * @return int|null
     */
    public function getLikes(): ?int
    {
        return $this->likes;
    }

    /**
     * @param int|null $likes
     */
    public function setLikes(?int $likes): void
    {
        $this->likes = $likes;
    }

    /**
     * @return int|null
     */
    public function getDislikes(): ?int
    {
        return $this->dislikes;
    }

    /**
     * @param int|null $dislikes
     */
    public function setDislikes(?int $dislikes): void
    {
        $this->dislikes = $dislikes;
    }

    /**
     * @return object|null
     */
    public function getGenre(): ?object
    {
        return $this->genre;
    }

    /**
     * @param object $genre
     */
    public function setGenre(object $genre): void
    {
        $this->genre = $genre;
    }

    /**
     * @return object|null
     */
    public function getAuthor(): ?object
    {
        return $this->author;
    }

    /**
     * @param object $author
     */
    public function setAuthor(object $author): void
    {
        $this->author = $author;
    }

    /**
     * @return string|null
     */
    public function getAvatar(): ?string
    {
        $avatar = $this->avatar;

        $https = isset($_SERVER['HTTPS']) ? 'https://' : 'http://';
        $host = $https . $_SERVER['HTTP_HOST'];

        $nullImageDir = $host . self::NULL_IMAGE_DIR;
        $nullImagePath = $nullImageDir . self::NULL_IMAGE;

        $uploadImagesDir = $host . self::UPLOAD_IMAGES_DIR;
        $uploadImagePath = $uploadImagesDir . $avatar;

        $avatar = $avatar ? $uploadImagePath : $nullImagePath;

        return $avatar;
    }

    /**
     * @param string|null $avatar
     */
    public function setAvatar(?string $avatar): void
    {
        $this->avatar = $avatar;
    }

    /**
     * @return object
     */
    public function getUser(): object
    {
        return $this->user;
    }

    /**
     * @param object $user
     */
    public function setUser(object $user): void
    {
        $this->user = $user;
    }

    public function toArray()
    {
        $genres = [];

        foreach ($this->getGenre() as $genre) {
            $genres[$genre->getId()] = $genre->getName();
        }

        $genresStr = implode(',', $genres);

        $authors = [];

        foreach ($this->getAuthor() as $author) {
            $authors[$author->getId()] = $author->getName();
        }

        $authorsStr = implode(',', $authors);

        return [
            'id' => $this->getId(),
            'name' => $this->getName(),
            'releaseDate' => $this->getReleaseDate()->format('Y-m-d H:i:s'),
            'catalogEntryDate' => $this->getCatalogEntryDate()->format('Y-m-d H:i:s'),
            'rating' => $this->getRating(),
            'genre' => $genresStr,
            'author' => $authorsStr,
        ];
    }
}
