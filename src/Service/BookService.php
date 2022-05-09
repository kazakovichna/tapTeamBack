<?php

namespace App\Service;
use App\Entity\Author;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Repository\AuthorRepository;
use App\Repository\BookRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\Book;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactory;
use Symfony\Component\Serializer\Mapping\Loader\AnnotationLoader;
use Doctrine\Common\Annotations\AnnotationReader;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use function Sodium\add;

class BookService
{
    private $bookRepository;
    private $authorRepository;
    private $entityManager;

    public function __construct(BookRepository $bookRepository, AuthorRepository $authorRepository, ManagerRegistry $doctrine)
    {
        $this->bookRepository = $bookRepository;
        $this->authorRepository = $authorRepository;
        $this->entityManager = $doctrine->getManager();
    }

    public function getAllBooks(): array
    {
        $books = $this->bookRepository->findAll();

        $classMetadataFactory = new ClassMetadataFactory(new AnnotationLoader(new AnnotationReader()));
        $normalizer = new ObjectNormalizer($classMetadataFactory);
        $serializer = new Serializer([$normalizer], array(new jsonEncoder()));
        $newBook = $serializer->serialize($books, 'json', [ 'groups' => ['book', 'author']]);

        if ($books === null) {

            return [
                'data' => 'We just dont get data from dataBase',
                'status' => Response::HTTP_OK
            ];
        }

        return [
            'data' =>   $newBook,
            'status' => Response::HTTP_OK
        ];
    }

    /**
     * @throws OptimisticLockException
     * @throws ORMException
     */
    public function addBook(Request $request): array
    {
        $requestData = json_decode($request->getContent());
        if ($requestData === null) {

            return [
                'data' => 'Empty Object Data Error 500',
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR
            ];
        }

        // Валидируем имя и год для добавление в бд
        // Тут лучше использовать проверку чем мапинг в форму я считаю, потому что тут нет поля описания
        if ($requestData->bookName === null ||
            strlen($requestData->bookName) === 0 ||
            strlen($requestData->bookName) > 255 ||
            $requestData->bookYear === null ||
            strlen($requestData->bookYear) === 0 ||
            strlen($requestData->bookYear) > 4 ||
            strlen($requestData->chosenAuthor) === 0 ||
            strlen($requestData->chosenAuthor) > 255 ||
            !preg_match("/^\d+$/", $requestData->bookYear)) {

            return [
                'data' => 'Invalid Object Data Error 500',
                'status' => Response::HTTP_OK
            ];
        }
        if ($this->bookRepository->findOneBy(['bookName' => $requestData->bookName]) !== null) {
            return [
                'data' => 'Book already exist',
                'status' => Response::HTTP_OK
            ];
        }

        $curAuthor = $this->authorRepository->findOneBy(['authorName' => $requestData->chosenAuthor]);

        if ( $curAuthor === null ) {
            echo "we dont find any author, lets create a new one author";
            $authorData = new Author();
            $authorData->setAuthorName($requestData->chosenAuthor);
            $authorData->setBookCount(1);

            $this->authorRepository->add($authorData, true);

            $bookData = new Book();
            $bookData->setBookName($requestData->bookName);
            $bookData->setBookYear($requestData->bookYear);
            $bookData->addAuthorList($authorData);

            $this->bookRepository->add($bookData, true);

            $bookData->setAuthorCount(1);
            $this->entityManager->flush();
            return [
                'data' => 'New Book added successfully',
                'status' => Response::HTTP_OK
            ];
        }

        $bookData = new Book();
        $bookData->setBookName($requestData->bookName);
        $bookData->setBookYear($requestData->bookYear);
        $bookData->addAuthorList($curAuthor);
        $this->bookRepository->add($bookData, true);

        $bookData->setAuthorCount(1);
        $this->entityManager->flush();

        return [
            'data' => 'New Book added successfully',
            'status' => Response::HTTP_OK
        ];

    }

    public function updateBookSimpleData(Request $request, $id): array
    {
        $requestData = json_decode($request->getContent());
        if ($requestData === null) {
            return [
                'data' => 'Empty Object Data Error 500',
                'status' => Response::HTTP_OK
            ];
        }

        $updatedBook = $this->bookRepository->findOneBy(['id'=>$id]);

        if ($requestData->bookName === '' || strlen($requestData->bookName) > 255 ||
            $requestData->bookYear === '' || strlen($requestData->bookYear) > 4 ||
            !preg_match("/^\d+$/", $requestData->bookYear) ||
            $requestData->bookDescription === '' || strlen($requestData->bookDescription) > 255
        ) {
            return [
                'data' => 'Invalid Data',
                'status' => Response::HTTP_OK
            ];
        }

        // Обновление названия книги
        if ($requestData->bookName != $updatedBook->getBookName()) {
            $updatedBook->setBookName($requestData->bookName);
        }

        // Обновление описание книги
        if ($requestData->bookDescription != $updatedBook->getBookDescription()) {
            $updatedBook->setBookDescription($requestData->bookDescription);
        }

        // Обновление года издания книги
        if ($requestData->bookYear !== $updatedBook->getBookYear()) {
            $updatedBook->setBookYear($requestData->bookYear);
        }
        $this->entityManager->flush();

        return [
            'data' => 'Book simple data update successfully',
            'status' => Response::HTTP_OK
        ];
    }

    public function updateBookAuthorName(Request $request, $id): array
    {
        $requestData = json_decode($request->getContent());
        if ($requestData === null) {
            return [
                'data' => 'Empty Object Data Error 500',
                'status' => Response::HTTP_OK
            ];
        }

        $updatedAuthor = $this->authorRepository->findOneBy(['id' => $requestData->authorId]);

        if ($requestData->authorName === '' || strlen($requestData->authorName) > 255 ||
            $requestData->authorId === 0) {
            return [
                'data' => 'Invalid Data',
                'status' => Response::HTTP_OK
            ];
        }

        $updatedAuthor->setAuthorName($requestData->authorName);
        $this->entityManager->flush();

        return [
            'data' => 'Book author name update successfully',
            'status' => Response::HTTP_OK
        ];
    }

    public function updateBookDeleteAuthor($request, $id): array
    {
        $requestData = json_decode($request->getContent());
        if ($requestData === null) {

            return [
                'data' => 'Empty Object Data Error 500',
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR
            ];
        }

        $currentBook = $this->bookRepository->findOneBy(['id' => $id]);
        $removedAuthor = $this->authorRepository->findOneBy(['id' => $requestData->authorId]);
        $oldCount = $removedAuthor->getBookCount();


        $currentBook->removeAuthorList($removedAuthor);
        $authorCount = count($currentBook->getAuthorList()->toArray());
        $currentBook->setAuthorCount($authorCount);
        $removedAuthor->setBookCount($oldCount - 1);

        $this->entityManager->flush();

        return [
            'data' => 'Book author name update successfully',
            'status' => Response::HTTP_OK
        ];
    }

    /**
     * @throws OptimisticLockException
     * @throws ORMException
     */
    public function updateBookAddAuthor(Request $request, $id): array
    {
        $requestData = json_decode($request->getContent());
        if ($requestData === null) {

            return [
                'data' => 'Empty Object Data Error 500',
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR
            ];
        }

        if (strlen($requestData->authorName) == 0 || strlen($requestData->authorName) > 255) {
            return [
                'data' => 'Invalid authorName',
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR
            ];
        }

        $updatedBook = $this->bookRepository->findOneBy(['id'=>$id]);
        if (!$updatedBook) {

            return [
                'data' => 'Book not found',
                'status' => Response::HTTP_NOT_FOUND
            ];
        }

        $addAuthor = $this->authorRepository->findOneBy(['authorName'=>$requestData->authorName]);
        if ($addAuthor === null) {
            // year author not found lets add some new
            $newAuthor = new Author();
            $newAuthor->setAuthorName($requestData->authorName);
            $newAuthor->setBookCount(0);

            $this->authorRepository->add($newAuthor, true);

            $updatedBook->addAuthorList($newAuthor);
            $this->entityManager->flush();

            $oldCount = $updatedBook->getAuthorCount();
            $updatedBook->setAuthorCount($oldCount + 1);

            $this->entityManager->flush();

            return [
                'data' => 'Add new author to dataBase and add it to this book)',
                'status' => Response::HTTP_OK
            ];
        }

        $updatedBook->addAuthorList($addAuthor);
        $this->entityManager->flush();

        $oldCount = $updatedBook->getAuthorCount();
        $updatedBook->setAuthorCount($oldCount + 1);
        $this->entityManager->flush();

        return [
            'data' => 'Add author to book successfully',
            'status' => Response::HTTP_OK
        ];
    }

    /**
     * @throws OptimisticLockException
     * @throws ORMException
     */
    public function deleteBook($id): array
    {
        $deletedBook = $this->bookRepository->findOneBy(["id"=>$id]);
        foreach ($deletedBook->getAuthorList() as $author) {
            $author->setBookCount($author->getBookCount() - 1);
        }
        $this->bookRepository->remove($deletedBook, true);

        if ($this->bookRepository->findOneBy(["id"=>$id]) !== null) {
            echo "book isn't deleted success";

            return [
                'data' => 'Book already exist in database',
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR
            ];
        }

        return [
            'data' => 'We deleted book successfully',
            'status' => Response::HTTP_OK
        ];
    }
}