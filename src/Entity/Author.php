<?php

namespace App\Entity;

use App\Repository\AuthorRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Serializer\Annotation\Groups;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\MaxDepth;

/**
 * @ORM\Entity(repositoryClass=AuthorRepository::class)
 */
class Author
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups("author")
     */
    private $id;

    /**
     * @ORM\Column(name="author_name", type="string", length=255, nullable=true)
     * @Groups("author")
     */
    private $authorName;

    /**
     * @ORM\Column(name="book_count", type="integer", nullable=true)
     * @Groups("author")
     */
    private $bookCount;

    /**
     * @ORM\ManyToMany(targetEntity="Book", mappedBy="authorList", cascade={"persist"})
     * @MaxDepth(2)
     * @Groups("book_detail")
     */
    private $booksList;

    public function __construct()
    {
        $this->booksList = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAuthorName(): ?string
    {
        return $this->authorName;
    }

    public function setAuthorName(?string $authorName): self
    {
        $this->authorName = $authorName;

        return $this;
    }

    /**
     * @return Collection<int, Book>
     */
    public function getBooksList(): Collection
    {
        return $this->booksList;
    }

    public function addBooksList(Book $booksList): self
    {
        if (!$this->booksList->contains($booksList)) {
            $this->booksList[] = $booksList;
            $booksList->addAuthorList($this);
        }

        return $this;
    }

    public function removeBooksList(Book $booksList): self
    {
        if ($this->booksList->removeElement($booksList)) {
            $booksList->removeAuthorList($this);
        }

        return $this;
    }

    public function getBookCount(): ?int
    {
        return $this->bookCount;
    }

    public function setBookCount(?int $bookCount): self
    {
        $this->bookCount = $bookCount;

        return $this;
    }
}
