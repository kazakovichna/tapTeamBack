<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\Response;
use App\Repository\BookRepository;

class BookService extends BookRepository
{
    public function getAllBooks(): Response
    {
        $books = $this->findAll();

        $bookResponseMas = [];

        foreach ($books as $book) {
            $bookJsonProto = new \stdClass();

            $bookJsonProto->book_id = $book->getId();
            $bookJsonProto->book_name = $book->getBookName();
            $bookJsonProto->book_descr = $book->getBookDescr();
            $bookJsonProto->book_year = $book->getBookYear();

            $bookAuthorMas = [];
            foreach ($book->getAuthorList()->toArray() as $authArr) {
                $authProto = new \stdClass();

                $authProto->author_id = $authArr->getId();
                $authProto->author_name = $authArr->getAuthorName();
                $bookAuthorMas[] = $authProto;
            }
            $bookJsonProto->book_authorList = $bookAuthorMas;

            $bookResponseMas[] = $bookJsonProto;
        }

//        echo json_encode($bookResponseMas);

        return new Response(
            json_encode($bookResponseMas),
            Response::HTTP_OK,
            ['content-type'=> 'json']
        );
    }
}