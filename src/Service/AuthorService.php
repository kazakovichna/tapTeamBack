<?php

namespace App\Service;

use App\Repository\AuthorRepository;
use App\Entity\Author;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Exception;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Persistence\ManagerRegistry;

class AuthorService
{
    /**
     * @var AuthorRepository
     */
    private $authorRepository;

    /**
     * @var ObjectManager
     */
    private $entityManager;

    public function __construct(ManagerRegistry $doctrine, AuthorRepository $authorRepository)
    {
        $this->authorRepository = $authorRepository;
        $this->entityManager = $doctrine->getManager();
    }

    public function getAllAuthorSer(): string
    {
        $authors = $this->authorRepository->findAll();
        if ($authors === null) {
            return "No authors was found";
        }

        $authorsMas = [];

        foreach ($authors as $author) {
            $authorJsonProto = new \stdClass();

            $authorJsonProto->author_id = $author->getId();
            $authorJsonProto->author_name = $author->getAuthorName();
            $authorJsonProto->author_book = $author->getBookCount();

            $authorsMas[] = $authorJsonProto;
        }

        return json_encode($authorsMas);
    }

    /**
     * @throws OptimisticLockException
     * @throws ORMException
     */
    public function addAuthor(Request $request): string
    {
        // Получаем данные из запроса
        $requestData = json_decode($request->getContent());
        echo $requestData->author_name;
        if ($requestData === null) {
            return 'Empty Object Data Error 500';
        }
        // Проверяем нет ли уже такого автора
        if ($this->authorRepository->findOneBy(['author_name'=>$requestData->author_name]) !== null) {
            return 'Author already exist error 500';
        }
        // Валидируем данные на длинну от 1 до 255
        if (strlen($requestData->author_name) === 0 ||
            strlen($requestData->author_name) > 255) {
            return "Invalid data";
        }
        // Сохраняем данные в базу данных
        $authorDB = new Author();
        $authorDB->setAuthorName($requestData->author_name);
        $this->authorRepository->add($authorDB, true);

        return "Author add successfully";
    }

    /**
     * @throws OptimisticLockException
     * @throws ORMException
     */
    public function deleteAuthor($id): string
    {
        $deletedAuth = $this->authorRepository->findOneBy(['id'=>$id]);
        if ($deletedAuth === null) {
            return "Author not found, maybe he is already deleted";
        }
        $this->authorRepository->remove($deletedAuth, true);
        if ($this->authorRepository->findOneBy(['id'=>$id]) !== null) {
            return "Author still not deleted, some error here hah";
        }

        return "Author delete successfully";
    }

    /**
     * @throws Exception
     */
    public function specialSQL(): string
    {
        $sql = "SELECT book_name FROM book AS b 
                INNER JOIN book_author AS ba ON b.id = ba.book_id 
                GROUP BY b.book_name HAVING count(ba.book_id) > 2";

        $connParams = [
            'url' => 'mysql://root:mazashib@127.0.0.1:3306/basic_crud_db'
        ];
        $conn = DriverManager::getConnection($connParams);
        $stmt = $conn->prepare($sql);
        $resultSet = $stmt->executeQuery();

        return json_encode($resultSet->fetchAllAssociative());
    }

    public function specialORM(): string
    {
        $queryBuilder =  $this->entityManager->createQueryBuilder();
//        $query = $queryBuilder->select(array('b'))
//            ->from('App:Book', 'b')
//            ->where(count(b.authorList) > 2)
//            ->groupBy('b.book_name')
//            ->having('count(ba) > 2')
//            ->getQuery();
//
//
//        var_dump($query->getResult());

        return '';
    }
}