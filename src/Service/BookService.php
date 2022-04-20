<?php

namespace App\Service;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Repository\AuthorRepository;
use App\Repository\BookRepository;
use App\Entity\Book;
use Doctrine\Persistence\ManagerRegistry;

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
        echo 'hello we are in the prod';
        $books = $this->bookRepository->findAll();

        $bookResponseMas = [];

        foreach ($books as $book) {
            $bookJsonProto = new \stdClass();

            $bookJsonProto->book_id = $book->getId();
            $bookJsonProto->book_name = $book->getBookName();
            $bookJsonProto->book_description = $book->getBookDescription();
            $bookJsonProto->book_year = $book->getBookYear();

            $bookAuthorMas = [];
            foreach ($book->getAuthorList()->toArray() as $authArr) {
                $authProto = new \stdClass();

                $authProto->author_id = $authArr->getId();
                $authProto->author_name = $authArr->getAuthorName();
                $bookAuthorMas[] = $authProto;
            }
            $bookJsonProto->book_authorList = $bookAuthorMas;

            $bookResponseMas[] = $bookJsonProto;
        }

        return [
            'data' => json_encode($bookResponseMas),
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
        if ($requestData->book_name === null ||
            strlen($requestData->book_name) === 0 ||
            strlen($requestData->book_name) > 255 ||
            $requestData->book_year === null ||
            strlen($requestData->book_year) === 0 ||
            strlen($requestData->book_year) > 4 ||
            !preg_match("/^\d+$/", $requestData->book_year)) {
            return [
                'data' => 'Invalid Object Data Error 500',
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR
            ];
        }

        $bookData = new Book();
        $bookData->setBookName($requestData->book_name);
        $bookData->setBookYear($requestData->book_year);

        $this->bookRepository->add($bookData, true);

        return [
            'data' => 'New Book added successfully',
            'status' => Response::HTTP_OK
        ];

    }

    public function updateBook(Request $request, $id): array
    {
        // Получает тело запроса и проверяем что оно не пустое, так как нам нужно обновляться
        $requestData = json_decode($request->getContent());
        if ($requestData === null) {
            return [
                'data' => 'Empty Object Data Error 500',
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR
            ];
        }

        // Получаем книгу из бд и проверяем что он сущесвтует
        $book = $this->bookRepository->findOneBy(['id'=>$id]);
        if (!$book) {
            return [
                'data' => 'Book not found',
                'status' => Response::HTTP_NOT_FOUND
            ];
        }

        // Проверка всех данных на длину пустоту сразу чтобы потом не проверять
        $isDataValid = $this->validateRequestData($requestData);
        if ($isDataValid === false) {
            return [
                'data' => 'Invalid data',
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR
            ];
        }

        return $this->updateAuthorsOfBook($requestData, $book);
    }

    // Данная функция проверяет данные которые пришли для обновления книги
    // Проверяет Имя, Описание на длину 0 < X <= 255,
    // Год на длину от 0 до 4 и на то что он состоит из цифр
    // А так же что массив авторов не пустой, у книги должен быть автор
    // У Голандца должен быть капитан
    public function validateRequestData(\stdClass $requestData): bool {
        if ($requestData->book_name === '' ||
            strlen($requestData->book_name) > 255 ||
            strlen($requestData->book_description) > 255 ||
            $requestData->book_year === '' ||
            !preg_match("/^\d+$/", $requestData->book_year) ||
            strlen($requestData->book_year <= 4) ||
            count($requestData->book_authorList) === 0) {
            return false;
        }

        foreach ($requestData->book_authorList as $auth) {
            if (strlen($auth->author_name) === 0 || strlen($auth->author_name) > 255) {
                return false;
            }
        }

        return true;
    }

    // Эта функция обновляет массив авторов и проверяет его на все условия:
    // Добавление автора, изменение имени автора, удаление автора.
    public function updateAuthorsOfBook(\stdClass $requestData,Book $book): array
    {
        // Обновление имени книги
        if ($requestData->book_name != $book->getBookName()) {
            $book->setBookName($requestData->book_name);
            $this->entityManager->flush();
        }

        // Обновление описание книги
        if ($requestData->book_description != $book->getBookDescription()) {
            $book->setBookDescription($requestData->book_description);
            $this->entityManager->flush();
        }

        // Обновление года издания книги
        if ($requestData->book_year !== $book->getBookYear()) {
            $book->setBookYear($requestData->book_year);
            $this->entityManager->flush();
        }

        // Далее идет самое сложное проверка и обновление авторов у книги

        // Part 1
        // Проверка на удаление автора.
        // Проверим и сравним длину входящих и имеющихся данных.
        // Если не равны и входящий массив меньше, то найти того автора и отвязать его
        if (count($requestData->book_authorList) < count($book->getAuthorList())) {
            foreach ($book->getAuthorList() as $bookOne) {
                $deletedAuth = null;
                foreach ($requestData->book_authorList as $item => $auth) {
                    if ($bookOne->getId() === $auth->author_id) {
                        $deletedAuth = $auth->author_id;
                        break;
                    }
                }
                if ($deletedAuth === null) {
                    $authToRemove = $this->authorRepository->findOneBy(['author_name'=>$bookOne->getAuthorName()]);

                    $book->removeAuthorList($authToRemove);
                    $oldBookCount = $authToRemove->getBookCount();
                    $authToRemove->setBookCount($oldBookCount - 1);
                    $this->entityManager->flush();
                }
            }
            return [
                'data' => 'Author Delete Success',
                'status' => Response::HTTP_OK
            ];
        }

        // Part 2
        // Проверка на обновление или добавление автора
        foreach ($requestData->book_authorList as $auth) {
            $isAuthorExistInBook = false;

            foreach ($book->getAuthorList() as $dbAuth) {

                // Если совпадает id, name автора то ничего не делать
                if ($auth->author_name === $dbAuth->getAuthorName() && $auth->author_id === $dbAuth->getId()) {
                    $isAuthorExistInBook = true;

                    break;

                    // Если пользователь хочет добавить нового автора но он написал тоже самое имя ничего не произойдет
                } elseif ($auth->author_name === $dbAuth->getAuthorName() && $auth->author_id === null) {
                    // Выход из этого цикла
                    continue;

                    // Если пользователь хочет поменять имя автора прямо в списке книг, меняем в бд
                } elseif ($auth->author_name !== $dbAuth->getAuthorName() && $auth->author_id === $dbAuth->getId()) {
                    echo 'I see you decide to change some author name so okay I gonna remember it))';
                    $isAuthorExistInBook = true;

                    $dbAuth->setAuthorName($auth->author_name);
                    $this->entityManager->flush();

                    return [
                        'data' => 'Author name updated',
                        'status' => Response::HTTP_OK
                    ];
                }
            }

            // Если автора нет в списке у книги, то мы ищем его в бд и если находим, то добавляем к массиву
            if ($isAuthorExistInBook === false) {
                $authDB = $this->authorRepository->findOneBy(['author_name'=>$auth->author_name]);
                if ($authDB != null ) {
//                    echo 'we are find right author in dataBase let add him to book';
                    $book->addAuthorList($authDB);
                    $this->entityManager->flush();
                    return [
                        'data' => 'Author add to db',
                        'status' => Response::HTTP_OK
                    ];
                } else {
                    return [
                        'data' => 'Author doesnt exist in database please create him first',
                        'status' => Response::HTTP_NOT_FOUND
                    ];
                }
            }
        }
        return [
            'data' => 'All up to date',
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
        $this->bookRepository->remove($deletedBook, true);

        if ($this->bookRepository->findOneBy(["id"=>$id]) !== null) {
            echo "book isn't deleted success";
            return [
                'data' => 'Book steel exist in database',
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR
            ];
        }
        return [
            'data' => 'We deleted book successfully',
            'status' => Response::HTTP_OK
        ];
    }
}