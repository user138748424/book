<?php

namespace App\Entity;

use App\Repository\AuthorRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=AuthorRepository::class)
 */
class Author
{
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
    private $bornDate;

    /**
     * @ORM\Column(type="string", nullable=false)
     * @Assert\Length(min=1, max=255)
     */
    private $gender;

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
    public function getBornDate(): ?\DateTimeInterface
    {
        return $this->bornDate;
    }

    /**
     * @param \DateTimeInterface $releaseDate
     */
    public function setBornDate(\DateTimeInterface $bornDate): void
    {
        $this->bornDate = $bornDate;
    }

    /**
     * @return string|null
     */
    public function getGender(): ?string
    {
        return $this->gender;
    }

    /**
     * @param string|null $gender
     */
    public function setGender(?string $gender): void
    {
        $this->gender = $gender;
    }

    public function toArray()
    {
        return [
            'id' => $this->getId(),
            'name' => $this->getName(),
            'bornDate' => $this->getBornDate()->format('Y-m-d H:i:s'),
            'gender' => $this->getGender(),
        ];
    }

    public function __toString()
    {
        return $this->getName();
    }
}
