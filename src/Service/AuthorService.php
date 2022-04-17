<?php

namespace App\Service;

use App\Repository\AuthorRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\Author;
use Symfony\Component\HttpFoundation\Response;

class AuthorService extends AuthorRepository
{
    public function getAllAuthorSer(AuthorRepository $authRep): Response
    {
        $authors = $authRep->findAll();
        if ($authors === null) {
            return new Response(
                "No authors was found",
                Response::HTTP_NOT_FOUND,
                ['content-type'=> 'json']
            );
        }

        $authorsMas = [];

        foreach ($authors as $author) {
            $authorJsonProto = new \stdClass();

            $authorJsonProto->author_id = $author->getId();
            $authorJsonProto->author_name = $author->getAuthorName();

            $authorsMas[] = $authorJsonProto;
        }

        return new Response(
            json_encode($authorsMas),
            Response::HTTP_OK,
            ['content-type'=> 'json']
        );
    }
}