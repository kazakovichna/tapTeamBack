<?php

namespace App\Entity;

use App\Repository\AuthorRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Serializer\Annotation\Groups;
use Doctrine\ORM\Mapping as ORM;

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
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups("author")
     */
    private $author_name;

    /**
     * @ORM\ManyToMany(targetEntity=Book::class, mappedBy="authorList")
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
        return $this->author_name;
    }

    public function setAuthorName(?string $author_name): self
    {
        $this->author_name = $author_name;

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
}
