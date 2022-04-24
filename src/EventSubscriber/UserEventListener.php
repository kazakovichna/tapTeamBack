<?php

namespace App\EventSubscriber;

use App\Entity\Author;
use App\Entity\Book;
use Doctrine\DBAL\DriverManager;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use function Doctrine\DBAL\Connection;

class UserEventListener
{
    /**
     * @throws \Doctrine\DBAL\Exception
     */
    public function postUpdate(Book $book, LifecycleEventArgs $args): void
    {
        $this->checkAuthorBookNumber($args);
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    public function checkAuthorBookNumber(LifecycleEventArgs $args): void
    {
        $argData = $args->getObject();

        if (($argData instanceof Book) === false) {
            echo 'It is not a book Mister whats the deal';
            return;
        }

        foreach ($argData->getAuthorList()->toArray() as $bookAuthor) {

            $entityManager = $args->getObjectManager()->getRepository(Author::class);
            $author = $entityManager->findOneBy(['author_name'=>$bookAuthor->getAuthorName()]);

            echo " author id " . $author->getId() . " author name " . $author->getAuthorName();

            $sql = "UPDATE author SET book_count =
                  (SELECT count(*) FROM book_author
                  WHERE author_id = ?) WHERE id = ?";

            $connParams = [
                'url' => 'mysql://njMJc055yS:qh9OaFysQc@remotemysql.com:3306/njMJc055yS'
            ];


            $conn = DriverManager::getConnection($connParams);
            $stmt = $conn->prepare($sql);
            $stmt->bindValue(1, $author->getId());
            $stmt->bindValue(2, $author->getId());

            $resultSet = $stmt->executeQuery();

            echo 'author count = '. $author->getBookCount();
        }

    }
}