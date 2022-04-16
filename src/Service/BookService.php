<?php

namespace App\Service;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\AuthorRepository;
use App\Repository\BookRepository;
use App\Entity\Book;

class BookService extends BookRepository
{
    public function getAllBooks(BookRepository $bookRep): Response
    {
        $books = $bookRep->findAll();

        $bookResponseMas = [];

        foreach ($books as $book) {
            $bookJsonProto = new \stdClass();

            $bookJsonProto->book_id = $book->getId();
            $bookJsonProto->book_name = $book->getBookName();
            $bookJsonProto->book_descr = $book->getBookDescr();
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

//        echo json_encode($bookResponseMas);

        return new Response(
            json_encode($bookResponseMas),
            Response::HTTP_OK,
            ['content-type'=> 'json']
        );
    }

    public function updateBook(Request $request, $id, $bookRep, $authRep, $entityManager): Response
    {
        // Получает тело запроса и проверяем что оно не пустое, так как нам нужно обновляться
        $requestData = json_decode($request->getContent());
        if ($requestData === null) {
            return new Response(
                'Empty Object Data Error 500',
                Response::HTTP_INTERNAL_SERVER_ERROR,
                [['content-type'=> 'json']]
            );
        }

        // Получаем книгу из бд и проверяем что он сущесвтует
        $book = $bookRep->findOneBy(['id'=>$id]);
        if (!$book) {
            return new Response(
                'Book not found',
                Response::HTTP_NOT_FOUND,
                [['content-type'=> 'json']]
            );
        }

        // Делаем блок транзакции,
        // чтобы выкинуть все сразу если что-то пойдет не так и причем с одной ошибкой
        // В нем проверять данные запроса на коректность и проверять таблицу авторов
        try {
            $isDataValid = $this->validateRequestData($requestData);
            if ($isDataValid === false) {
                throw new \Exception();
            }
            $updateCurFieldRes = $this->updateCurFieldBook($requestData, $book, $authRep, $entityManager);

        }catch (\Exception $e) {
            return new Response(
                'Invalid Data',
                Response::HTTP_UNPROCESSABLE_ENTITY,
                ['content-type'=> 'json']
            );
        }

        return new Response(
            $updateCurFieldRes
        );
    }

    // Данная функция проверяет данные которые пришли для обновления книги
    // Проверяет Имя, Описание на длину 0 < X <= 255,
    // Год на длину от 0 до 4 и на то что он состоит из цифр
    // А так же что массив авторов не пустой, у книги должен быть автор
    // У Голандца должен быть капитан
    public function validateRequestData(\stdClass $requestData): bool {
        if ($requestData->book_name === '' ||
            strlen($requestData->book_name) > 255 ||
            strlen($requestData->book_descr) > 255 ||
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
    // Обновляет в бд данные о конкретной книге
    // Все входные данные уже провалидированные тут этого делать не нужно
    // Причем изменение может касаться только одного поля
    // Название книги, Описание, Год, Или одно поле из массива автора
    public function updateCurFieldBook(\stdClass $requestData,Book $book,AuthorRepository $authRep,EntityManagerInterface $entityManager): Response {

        // Эта функция обновляет простые поля Имя, Описание, Год книги.
        $this->updateSimpleBookField($requestData, $book, $entityManager);

        // Эта функция обновляет массив авторов и проверяет его на все условия.
        $this->updateAuthorsOfBook($requestData, $book, $authRep, $entityManager);

        return new Response(
            "Update Success",
            Response::HTTP_OK,
            ['content-type'=> 'json']
        );
    }
    // Эта функция обновляет простые поля Имя, Описание, Год книги. Просто проверяет есть ли изменения.
    public function updateSimpleBookField(\stdClass $requestData,Book $book, EntityManagerInterface $entityManager)
    {
        // Обновление имени книги
        if ($requestData->book_name != $book->getBookName()) {
            $book->setBookName($requestData->book_name);
            $entityManager->flush();

            return new Response(
                "Book name updated",
                Response::HTTP_OK,
                ['content-type'=> 'json']
            );
        }

        // Обновление описание книги
        if ($requestData->book_descr != $book->getBookDescr()) {
            $book->setBookDescr($requestData->book_descr);
            $entityManager->flush();

            return new Response(
                "Book descr updated",
                Response::HTTP_OK,
                ['content-type'=> 'json']
            );
        }

        // Обновление года издания книги
        if ($requestData->book_year !== $book->getBookYear()) {
            $book->setBookYear($requestData->book_year);
            $entityManager->flush();

            return new Response(
                "Book year updated",
                Response::HTTP_OK,
                ['content-type'=> 'json']
            );
        }
        return 'No fields need update';
    }
    // Эта функция обновляет массив авторов и проверяет его на все условия:
    // Добавление автора, изменение имени автора, удаление автора.
    public function updateAuthorsOfBook(\stdClass $requestData,Book $book,AuthorRepository $authRep,EntityManagerInterface $entityManager)
    {
        // Проверка на удаление элемента.
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
                    $authToRemove = $authRep->findOneBy(['author_name'=>$bookOne->getAuthorName()]);

                    $book->removeAuthorList($authToRemove);
                    $entityManager->flush();
                }
            }
            return new Response(
                "Okay we delete some author",
                Response::HTTP_OK,
                ['content-type'=> 'json']
            );
        }

        foreach ($requestData->book_authorList as $auth) {
            $isAuthorExistInBook = false;

            foreach ($book->getAuthorList() as $dbAuth) {

                // Если совпадает id, name автора то ничего не делать
                if ($auth->author_name === $dbAuth->getAuthorName() && $auth->author_id === $dbAuth->getId()) {
                    $isAuthorExistInBook = true;

                    break;

                    // Если пользователь хочет добавить нового автора но он написал тоже самое имя ничего не произойдет
                } elseif ($auth->author_name === $dbAuth->getAuthorName() && $auth->author_id === null) {
                    return new Response(
                        "Author already exist in bookList",
                        Response::HTTP_NOT_FOUND,
                        ['content-type'=> 'json']
                    );

                    // Если пользователь хочет поменять имя автору прямо в списке книг
                } elseif ($auth->author_name !== $dbAuth->getAuthorName() && $auth->author_id === $dbAuth->getId()) {
                    echo 'I see you decide to change some author name so okay I gonna remember it))';
                    $isAuthorExistInBook = true;

                    $dbAuth->setAuthorName($auth->author_name);
                    $entityManager->flush();

                    return new Response(
                        "Author name updated",
                        Response::HTTP_OK,
                        ['content-type'=> 'json']
                    );
                }
            }

            // Если автора нет в списке у книги, то мы ищем его в бд и если находим, то добавляем к массиву
            if ($isAuthorExistInBook === false) {
                $authDB = $authRep->findOneBy(['author_name'=>$auth->author_name]);
                if ($authDB != null ) {
                    echo 'we are find right author in dataBase let add him to book';
                    $book->addAuthorList($authDB);
                    $entityManager->flush();
                } else {
                    return new Response(
                        "Author doesn't exist in database please create him first",
                        Response::HTTP_NOT_FOUND,
                        ['content-type'=> 'json']
                    );
                }
            }
        }
        return 'Okay no update here';
    }
}