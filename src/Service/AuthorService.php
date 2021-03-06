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

use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactory;
use Symfony\Component\Serializer\Mapping\Loader\AnnotationLoader;
use Doctrine\Common\Annotations\AnnotationReader;
use Symfony\Component\Serializer\Encoder\JsonEncoder;

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

        $classMetadataFactory = new ClassMetadataFactory(new AnnotationLoader(new AnnotationReader()));
        $normalizer = new ObjectNormalizer($classMetadataFactory);
        $serializer = new Serializer([$normalizer], array(new jsonEncoder()));

        $newAuthors = $serializer->serialize($authors, 'json', ['groups' => ['author']]);

        if ($authors === null) {
            return "No authors was found";
        }

        return $newAuthors;
    }

    /**
     * @throws OptimisticLockException
     * @throws ORMException
     */
    public function addAuthor(Request $request): string
    {
        // Получаем данные из запроса
        $requestData = json_decode($request->getContent());
        echo $requestData->authorName;
        if ($requestData === null) {
            return 'Empty Object Data Error 500';
        }
        // Проверяем нет ли уже такого автора
        if ($this->authorRepository->findOneBy(['authorName'=>$requestData->authorName]) !== null) {
            return 'Author already exist error 500';
        }
        // Валидируем данные на длинну от 1 до 255
        if (strlen($requestData->authorName) === 0 ||
            strlen($requestData->authorName) > 255) {
            return "Invalid data";
        }
        // Сохраняем данные в базу данных
        $authorDB = new Author();
        $authorDB->setAuthorName($requestData->authorName);
        $authorDB->setBookCount(0);
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
                GROUP BY b.id HAVING count(ba.book_id) > 2";

        $connParams = [
            'url' => $_ENV['DATABASE_URL']
        ];
        $conn = DriverManager::getConnection($connParams);
        $stmt = $conn->prepare($sql);
        $resultSet = $stmt->executeQuery();

        return json_encode($resultSet->fetchAllAssociative());
    }

    public function specialORM(): string
    {
        $queryBuilder =  $this->entityManager->createQueryBuilder();
        $query = $queryBuilder->select(array('b.bookName'))
            ->from('App:Book', 'b')
            ->leftJoin('b.authorList', 'a')
            ->having('COUNT(a.id) > 2')
            ->groupBy('b.id')
            ->getQuery();

        return json_encode($query->getResult());
    }
}