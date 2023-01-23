<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\ORM\Mapping as ORM;
use FOS\UserBundle\Model\User as BaseUser;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=UserRepository::class)
 * @ORM\Table(name="fos_user")
 */
class User extends BaseUser
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="string", nullable=true)
     * @Assert\Length(min=1, max=255)
     */
    private $avatar;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Book", mappedBy="user")
     */
    private $book;

    public function __construct()
    {
        parent::__construct();
        // your own logic
    }

    /**
     * @return string|null
     */
    public function getAvatar(): ?string
    {
        return $this->avatar;
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
    public function getBook(): object
    {
        return $this->book;
    }

    /**
     * @param object $user
     */
    public function setBook(object $book): void
    {
        $this->book = $book;
    }
}
