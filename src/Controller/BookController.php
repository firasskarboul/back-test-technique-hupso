<?php

namespace App\Controller;

use App\Entity\Book;
use App\Repository\BookRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\HttpFoundation\Response;

class BookController extends AbstractController
{
    #[Route('/api/books', name: 'add_book', methods: ['POST'])]
    public function addBook(Request $request, EntityManagerInterface $entityManager, ValidatorInterface $validator): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $book = new Book();
        $book->setTitle($data['title']);
        $book->setDescription($data['description']);
        $book->setAuthor($data['author']);
        $book->setPublishedAt(new \DateTimeImmutable($data['publishedAt']));
        $book->setCategory($data['category']);

        if (!$data) {
            return new JsonResponse(['message' => 'Invalid JSON'], Response::HTTP_BAD_REQUEST);
        }

        $errors = $validator->validate($book);
        if (count($errors) > 0) {
            $errorsString = (string) $errors;

            return new JsonResponse(['message' => $errorsString], Response::HTTP_BAD_REQUEST);
        }

        try {
            $entityManager->persist($book);
            $entityManager->flush();
        } catch (\Exception $e) {
            return new JsonResponse(['message' => 'An error occurred while saving the book: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return new JsonResponse(['message' => 'Book added successfully'], Response::HTTP_CREATED);
    }

    #[Route('/api/books', name: 'get_all_books', methods: ['GET'])]
    public function getAllBooks(Request $request, BookRepository $bookRepository): JsonResponse
    {
        $title = $request->query->get('title');
        $category = $request->query->get('category');
        $publishedYear = $request->query->get('publishedYear');
        $available = $request->query->get('availability');

        try {
            $books = $bookRepository->findByFilters($title, $category, $publishedYear, $available);
        } catch (\Exception $e) {
            return new JsonResponse(['message' => 'An error occurred while retrieving books: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        $data = [];

        foreach ($books as $book) {
            $data[] = [
                'id' => $book['id'],
                'title' => $book['title'],
                'description' => $book['description'],
                'author' => $book['author'],
                'publishedAt' => (new \DateTime($book['published_at']))->format('Y-m-d'),
                'category' => $book['category'],
            ];
        }

        return new JsonResponse($data, Response::HTTP_OK);
    }

    #[Route('/api/books/{id}', name: 'get_book_by_id', methods: ['GET'])]
    public function getBookById(int $id, BookRepository $bookRepository): JsonResponse
    {
        try {
            $book = $bookRepository->find($id);

            if (!$book) {
                return new JsonResponse(['message' => 'Book not found'], Response::HTTP_NOT_FOUND);
            }
        } catch (NoResultException | NonUniqueResultException $e) {
            return new JsonResponse(['message' => 'An error occurred while retrieving the book: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        } catch (\Exception $e) {
            return new JsonResponse(['message' => 'An unexpected error occurred: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        $data = [
            'id' => $book->getId(),
            'title' => $book->getTitle(),
            'description' => $book->getDescription(),
            'author' => $book->getAuthor(),
            'publishedAt' => $book->getPublishedAt()->format('Y-m-d'),
            'category' => $book->getCategory(),
        ];

        return new JsonResponse($data, Response::HTTP_OK);
    }

    #[Route('/api/categories', name: 'get_all_categories', methods: ['GET'])]
    public function getAllCategories(BookRepository $bookRepository): JsonResponse
    {
        try {
            $categories = $bookRepository->findDistinctCategories();
        } catch (\Exception $e) {
            return new JsonResponse(['message' => 'An error occurred while retrieving categories: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        $data = array_map(function ($category) {
            return $category['category'];
        }, $categories);

        return new JsonResponse($data, Response::HTTP_OK);
    }

    #[Route('/api/years', name: 'get_all_years', methods: ['GET'])]
    public function getAllYears(BookRepository $bookRepository): JsonResponse
    {
        try {
            $years = $bookRepository->findDistinctPublicationYears();
        } catch (\Exception $e) {
            return new JsonResponse(['message' => 'An error occurred while retrieving publication years: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        $data = array_map(function ($year) {
            return $year['year'];
        }, $years);

        return new JsonResponse($data, Response::HTTP_OK);
    }
}
