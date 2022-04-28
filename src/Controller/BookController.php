<?php

namespace App\Controller;

use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\BookService;
use function Amp\Dns\resolver;

class BookController extends AbstractController
{
    private $bookService;

    public function __construct(BookService $bookService)
    {
        $this->bookService = $bookService;
    }

    /**
     * @Route("/book", name="app_book", methods={"GET"})
     */
    public function getAllBooks(): Response
    {
        $jsonResponse = $this->bookService->getAllBooks();

        return new Response(
            $jsonResponse['data'],
            $jsonResponse['status'],
            ['content-type'=> 'json']
        );
    }

    /**
     * @Route("/book", name="addBook", methods={"POST"})
     *
     * @param Request $request
     *
     * @return Response
     */
    public function addBook(Request $request): Response
    {
        $jsonResponse = $this->bookService->addBook($request);

        return new Response(
            $jsonResponse['data'],
            $jsonResponse['status'],
            ['content-type'=> 'json']
        );
    }

    /**
     * @Route("/book/{id}", name="update", methods={"POST"})
     *
     * @param Request $request
     * @param $id
     *
     * @return Response
     */
    public function updateBook(Request $request, $id): Response
    {
        $jsonResponse = $this->bookService->updateBook($request, $id);

        return new Response(
            $jsonResponse['data'],
            $jsonResponse['status'],
            ['content-type'=> 'json']
        );
    }

    /**
     * @Route("book/{id}", name="deleteBook", methods={"DELETE"})
     *
     * @param $id
     *
     * @return Response
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function deleteBook($id): Response
    {
        $jsonResponse = $this->bookService->deleteBook($id);

        return new Response(
            $jsonResponse['data'],
            $jsonResponse['status'],
            ['content-type'=> 'json']
        );
    }

    /**
     * @Route("book/{id}/updatesimpledata", name="updatesimpledata", methods={"POST"})
     *
     * @param Request $request
     * @param $id
     *
     * @return Response
     */
    public function updateBookSimpleData (Request $request, $id): Response
    {
        $jsonResponse = $this->bookService->updateBookSimpleData($request, $id);

        return new Response(
            $jsonResponse['data'],
            $jsonResponse['status'],
            ['content-type'=> 'json']
        );
    }

    /**
     * @Route ("book/{id}/updatebookauthorname", name="updatebookauthorname", methods={"POST"})
     *
     * @param Request $request
     * @param $id
     *
     * @return void
     */
    public function updateBookAuthorName (Request $request, $id): Response
    {
        $jsonResponse = $this->bookService->updateBookAuthorName($request, $id);

        return new Response(
            $jsonResponse['data'],
            $jsonResponse['status'],
            ['content-type'=> 'json']
        );
    }

    /**
     * @Route("book/{id}/updatebookdeleteauthor", name="updatebookdeleteauthor", methods={"POST"})
     *
     * @param Request $request
     * @param $id
     *
     * @return void
     */
    public function updateBookDeleteAuthor (Request $request, $id): Response
    {
        $jsonResponse = $this->bookService->updateBookDeleteAuthor($request, $id);

        return new Response(
            $jsonResponse['data'],
            $jsonResponse['status'],
            ['content-type'=> 'json']
        );
    }
    public function updateBookAddAuthor ()
    {}
}






