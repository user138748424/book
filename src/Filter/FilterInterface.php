<?php

namespace App\Filter;

interface FilterInterface
{
    public const START_RELEASE_DATE = 'startReleaseDate';
    public const END_RELEASE_DATE = 'endReleaseDate';
    public const GENRE = 'genre';
    public const AUTHOR = 'author';

    /**
     * @return string|null
     */
    public function getStartReleaseDate(): ?string;

    /**
     * @param string|null $startReleaseDate
     * @return $this
     */
    public function setStartReleaseDate(?string $startReleaseDate): self;

    /**
     * @return string|null
     */
    public function getEndReleaseDate(): ?string;

    /**
     * @param string|null $endReleaseDate
     * @return $this
     */
    public function setEndReleaseDate(?string $endReleaseDate): self;

    /**
     * @return string|null
     */
    public function getGenre(): ?string;

    /**
     * @param string|null $genre
     * @return $this
     */
    public function setGenre(?string $genre): self;

    /**
     * @return string|null
     */
    public function getAuthor(): ?string;

    /**
     * @param string|null $author
     * @return $this
     */
    public function setAuthor(?string $author): self;
}