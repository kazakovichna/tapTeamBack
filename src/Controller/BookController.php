<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\BookService;
use App\Repository\AuthorRepository;

class BookController extends AbstractController
{
    /**
     * @Route("/book", name="app_book", methods={"GET"})
     */
    public function getAllBooks(BookService $bookSer): Response
    {
        return $bookSer->getAllBooks();
    }
    /**
     * @Route("/book/{id}", name="update", methods={"POST"})
     */
    public function updateBook(BookService $bookSer): Response
    {
        return new Response(
          'Okay'
        );
    }
}
