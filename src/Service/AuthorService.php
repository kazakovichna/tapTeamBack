<?php

namespace App\Service;

use App\Repository\AuthorRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\Author;
use Symfony\Component\HttpFoundation\Response;

class AuthorService extends AuthorRepository
{
    public function setAuthor(): Response
    {
//        $authorAr = ['galard', 'Dostoevski', 'Grin', 'Flag', 'Dumas'];
//
//        $entityManager = $this->getEntityManager();
//
//        foreach ($authorAr as $author) {
//            $au = new Author();
//            $au->setAuthorName($author);
//            $entityManager->persist($au);
//        }
//        $entityManager->flush();
        return new Response(
            'Hello World'
        );
    }
}