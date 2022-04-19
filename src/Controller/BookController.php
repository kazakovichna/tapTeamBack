<?php

namespace App\Controller;

use App\Entity\Book;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\BookService;
use App\Repository\BookRepository;
use App\Repository\AuthorRepository;
use Doctrine\ORM\EntityManagerInterface;

class BookController extends AbstractController
{
    /**
     * @Route("/book", name="app_book", methods={"GET"})
     */
    public function getAllBooks(BookService $bookSer, BookRepository $bookRep): Response
    {
        return $bookSer->getAllBooks($bookRep);
    }

    /**
     * @Route("/book", name="addBook", methods={"POST"})
     *
     * @param BookService $bookSer
     * @param BookRepository $bookRep
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     *
     * @return Response
     */
    public function addBook(BookService $bookSer, BookRepository $bookRep, Request $request, EntityManagerInterface $entityManager): Response
    {
        return $bookSer->addBook($bookRep,  $request, $entityManager);
    }

    /**
     * @Route("/book/{id}", name="update", methods={"POST"})
     *
     * @param BookService $bookSer
     * @param BookRepository $bookRep
     * @param AuthorRepository $authRep
     * @param EntityManagerInterface $entityManager
     * @param Request $request
     * @param $id
     *
     * @return Response
     */
    public function updateBook(BookService $bookSer, BookRepository $bookRep, AuthorRepository $authRep, EntityManagerInterface $entityManager, Request $request, $id): Response
    {
        return $bookSer->updateBook($request, $id, $bookRep, $authRep, $entityManager);
    }

    /**
     * @Route("book/{id}", name="deleteBook", methods={"DELETE"})
     *
     * @param BookService $bookSer
     * @param BookRepository $bookRep
     * @param EntityManagerInterface $entityManager
     * @param $id
     *
     * @return Response
     */
    public function deleteBook(BookService $bookSer, BookRepository $bookRep, EntityManagerInterface $entityManager, $id): Response
    {
        return $bookSer->deleteBook($bookRep, $entityManager, $id);
    }
}