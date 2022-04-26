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
     * @ORM\Column(name="book_name", type="string", length=255, nullable=true)
     * @Groups("book")
     */
    private $bookName;

    /**
     * @ORM\Column(name="book_description", type="string", length=255, nullable=true)
     * @Groups("book")
     */
    private $bookDescription;

    /**
     * @ORM\Column(name="book_year", type="string", length=255, nullable=true)
     * @Groups("book")
     */
    private $bookYear;

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
        return $this->bookName;
    }

    public function setBookName(?string $bookName): self
    {
        $this->bookName = $bookName;

        return $this;
    }

    public function getBookDescription(): ?string
    {
        return $this->bookDescription;
    }

    public function setBookDescription(?string $bookDescription): self
    {
        $this->bookDescription = $bookDescription;

        return $this;
    }

    public function getBookYear(): ?string
    {
        return $this->bookYear;
    }

    public function setBookYear(?string $bookYear): self
    {
        $this->bookYear = $bookYear;

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
