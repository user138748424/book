<?php

namespace App\Filter;

use App\Filter\FilterInterface;

class Filter implements FilterInterface
{
    private $startReleaseDate;

    private $endReleaseDate;

    private $genre;

    private $author;

    /**
     * @inheritDoc
     */
    public function getStartReleaseDate(): ?string
    {
        return $this->startReleaseDate;
    }

    /**
     * @inheritDoc
     */
    public function setStartReleaseDate(?string $startReleaseDate): FilterInterface
    {
        $this->startReleaseDate = $startReleaseDate;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getEndReleaseDate(): ?string
    {
        return $this->endReleaseDate;
    }

    /**
     * @inheritDoc
     */
    public function setEndReleaseDate(?string $endReleaseDate): FilterInterface
    {
        $this->endReleaseDate = $endReleaseDate;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getGenre(): ?string
    {
        return $this->genre;
    }

    /**
     * @inheritDoc
     */
    public function setGenre(?string $genre): FilterInterface
    {
        $this->genre = $genre;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getAuthor(): ?string
    {
        return $this->author;
    }

    /**
     * @inheritDoc
     */
    public function setAuthor(?string $author): FilterInterface
    {
        $this->author = $author;

        return $this;
    }
}