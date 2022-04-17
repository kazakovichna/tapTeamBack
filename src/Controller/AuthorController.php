<?php

namespace App\Controller;

use App\Repository\AuthorRepository;
use App\Service\AuthorService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

class AuthorController extends AbstractController
{
    /**
     * @Route("/author", name="app_author", methods={"GET"})
     */
    public function getAllAuthor(AuthorService $authSer, AuthorRepository $authRep): Response
    {
        return $authSer->getAllAuthorSer($authRep);
    }

    /**
     * @Route("/author", name="add_author", methods={"POST"})
     * @param AuthorService $authSer
     * @param AuthorRepository $authRep
     * @param Request $request
     * @return Response
     */
    public function add_author(AuthorService $authSer, AuthorRepository $authRep, Request $request): Response
    {
        return $authSer->addAuthor($authRep, $request);
    }
}
