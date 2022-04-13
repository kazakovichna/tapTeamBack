<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\AuthorService;

class AuthorController extends AbstractController
{
    /**
     * @Route("/author", name="app_author")
     */
    public function getAllBooks(AuthorService $bookSer): Response
    {
        return $bookSer->setAuthor();
    }
}
