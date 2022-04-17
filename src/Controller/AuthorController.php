<?php

namespace App\Controller;

use App\Repository\AuthorRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\AuthorService;

class AuthorController extends AbstractController
{
    /**
     * @Route("/author", name="app_author", methods={"GET"})
     */
    public function getAllAuthor(AuthorService $authSer, AuthorRepository $authRep): Response
    {
        return $authSer->getAllAuthorSer($authRep);
    }
}
