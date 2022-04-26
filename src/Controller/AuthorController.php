<?php

namespace App\Controller;

use App\Service\AuthorService;
use Doctrine\DBAL\Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

class AuthorController extends AbstractController
{
    /**
     * @var AuthorService
     */
    private $authorService;

    public function __construct(AuthorService $authorService)
    {
        $this->authorService = $authorService;
    }

    /**
     * @Route("/author", name="appAuthor", methods={"GET"})
     */
    public function getAllAuthor(): Response
    {
        $jsonResponse = $this->authorService->getAllAuthorSer();

        return new Response(
            $jsonResponse,
            Response::HTTP_OK
        );
    }

    /**
     * @Route("/author", name="addAuthor", methods={"POST"})
     * @param Request $request
     * @return Response
     */
    public function addAuthor(Request $request): Response
    {
        $jsonResponse = $this->authorService->addAuthor($request);

        return new Response(
            $jsonResponse,
            Response::HTTP_OK
        );
    }

    /**
     * @Route("/author/{id}", name="deleteAuthor", methods={"DELETE"})
     *
     * @param $id
     *
     * @return Response
     */
    public function deleteAuthor($id): Response
    {
        $jsonResponse = $this->authorService->deleteAuthor($id);

        return new Response(
            $jsonResponse,
            Response::HTTP_OK
        );
    }

    /**
     * @Route("/special/sql", name="specialSQL", methods={"GET"})
     *
     * @return Response
     * @throws Exception
     */
    public function specialRequestSQl(): Response
    {
        $responseJson = $this->authorService->specialSQL();

        return new Response(
            $responseJson,
            Response::HTTP_OK
        );
    }

    /**
     * @Route("/special/orm", name="specialORM", methods={"GET"})
     *
     * @return Response
     */
    public function specialRequestORM(): Response
    {
        $responseJson = $this->authorService->specialORM();

        return new Response(
            $responseJson,
            Response::HTTP_OK
        );
    }
}
