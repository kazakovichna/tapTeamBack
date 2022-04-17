<?php

namespace App\Service;

use App\Repository\AuthorRepository;
use App\Entity\Author;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
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

    /**
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\ORMException
     */
    public function addAuthor(AuthorRepository $authRep, Request $request): Response
    {
        // Получаем данные из запроса
        $requestData = json_decode($request->getContent());
        echo $requestData->author_name;
        if ($requestData === null) {
            return new Response(
                'Empty Object Data Error 500',
                Response::HTTP_INTERNAL_SERVER_ERROR,
                [['content-type'=> 'json']]
            );
        }
        // Проверяем нет ли уже такого автора
        if ($authRep->findOneBy(['author_name'=>$requestData->author_name]) !== null) {
            return new Response(
                'Author already exist error 500',
                Response::HTTP_INTERNAL_SERVER_ERROR,
                [['content-type'=> 'json']]
            );
        }
        // Валидируем данные на длинну от 1 до 255
        if (strlen($requestData->author_name) === 0 ||
            strlen($requestData->author_name) > 255) {
            return new Response(
                "Invalid data",
                Response::HTTP_INTERNAL_SERVER_ERROR,
                ['content-type'=> 'json']
            );
        }
        // Сохраняем данные в базу данных
        $authorDB = new Author();
        $authorDB->setAuthorName($requestData->author_name);
        $authRep->add($authorDB, true);

        return new Response(
            "Author add successfully",
            Response::HTTP_OK,
            ['content-type'=> 'json']
        );
    }
}