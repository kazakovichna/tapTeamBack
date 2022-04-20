<?php

namespace App\Entity;

use App\Repository\BookRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Serializer\Annotation\Groups;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=BookRepository::class)
 */
class Book
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups("book")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups("book")
     */
    private $book_name;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups("book")
     */
    private $book_description;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups("book")
     */
    private $book_year;

//    /**
//     * @ORM\Column(type="blob", nullable=true)
//     */
//    private $book_img;

    /**
     * @ORM\ManyToMany(targetEntity=Author::class, inversedBy="booksList")
     */
    private $authorList;

    public function __construct()
    {
        $this->authorList = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getBookName(): ?string
    {
        return $this->book_name;
    }

    public function setBookName(?string $book_name): self
    {
        $this->book_name = $book_name;

        return $this;
    }

    public function getBookDescription(): ?string
    {
        return $this->book_description;
    }

    public function setBookDescription(?string $book_description): self
    {
        $this->book_description = $book_description;

        return $this;
    }

    public function getBookYear(): ?string
    {
        return $this->book_year;
    }

    public function setBookYear(?string $book_year): self
    {
        $this->book_year = $book_year;

        return $this;
    }

//    public function getBookImg()
//    {
//        return $this->book_img;
//    }
//
//    public function setBookImg($book_img): self
//    {
//        $this->book_img = $book_img;
//
//        return $this;
//    }

    /**
     * @return Collection<int, Author>
     */
    public function getAuthorList(): Collection
    {
        return $this->authorList;
    }

    public function addAuthorList(Author $authorList): self
    {
        if (!$this->authorList->contains($authorList)) {
            $this->authorList[] = $authorList;
        }

        return $this;
    }

    public function removeAuthorList(Author $authorList): self
    {
        $this->authorList->removeElement($authorList);

        return $this;
    }
}
