<?php

namespace App\Controller;

use App\Entity\Booking;
use App\Entity\Utilisateur;
use App\Repository\BookingRepository;
use App\Repository\BookRepository;
use App\Repository\UtilisateurRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class BookingController extends AbstractController
{

    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    #[Route('/api/bookings', name: 'get_all_bookings', methods: ['GET'])]
    public function getAllBookings(BookingRepository $bookingRepository, UtilisateurRepository $utilisateurRepository): JsonResponse
    {

        $user = $utilisateurRepository->find($this->security->getUser());
        if (!$user) {
            return new JsonResponse(['message' => 'User not found'], Response::HTTP_UNAUTHORIZED);
        }

        $bookings = $bookingRepository->findActiveByUser($user);
        $data = [];

        foreach ($bookings as $booking) {
            $book = $booking->getBook();
            $data[] = [
                'id' => $booking->getId(),
                'book' => [
                    'id' => $book->getId(),
                    'title' => $book->getTitle(),
                    'description' => $book->getDescription(),
                    'author' => $book->getAuthor(),
                    'publishedAt' => $book->getPublishedAt()->format('Y-m-d'),
                    'category' => $book->getCategory()
                ],
                'startDate' => $booking->getStartDate()->format('Y-m-d H:i:s'),
                'endDate' => $booking->getEndDate()->format('Y-m-d H:i:s'),
                'status' => $booking->getStatus(),
            ];
        }

        return new JsonResponse($data, Response::HTTP_OK);
    }

    #[Route('/api/bookings', name: 'add_booking', methods: ['POST'])]
    public function loanBook(
        Request $request,
        EntityManagerInterface $entityManager,
        ValidatorInterface $validator,
        BookRepository $bookRepository,
        BookingRepository $bookingRepository,
        UtilisateurRepository $utilisateurRepository
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);

        if (!$data) {
            return new JsonResponse(['message' => 'Invalid JSON'], Response::HTTP_BAD_REQUEST);
        }

        $book = $bookRepository->find($data['bookId']);
        if (!$book) {
            return new JsonResponse(['message' => 'Invalid Book ID'], Response::HTTP_BAD_REQUEST);
        }

        $user = $utilisateurRepository->find($this->security->getUser());
        if (!$user) {
            return new JsonResponse(['message' => 'User not found'], Response::HTTP_UNAUTHORIZED);
        }

        $startDate = new \DateTime($data['startDate']);
        $endDate = new \DateTime($data['endDate']);

        if (!$bookingRepository->isBookAvailable($book, $startDate, $endDate)) {
            return new JsonResponse(['message' => 'The book is not available for the requested period'], Response::HTTP_CONFLICT);
        }

        $booking = new Booking();
        $booking->setBook($book);
        $booking->setUser($user);
        $booking->setStartDate(new \DateTime($data['startDate']));
        $booking->setEndDate(new \DateTime($data['endDate']));
        $booking->setStatus('active');

        $errors = $validator->validate($booking);
        if (count($errors) > 0) {
            $errorsString = (string) $errors;

            return new JsonResponse(['message' => $errorsString], Response::HTTP_BAD_REQUEST);
        }

        try {
            $entityManager->persist($booking);
            $entityManager->flush();
        } catch (\Exception $e) {
            return new JsonResponse(['message' => 'An error occurred while saving the booking: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        $bookingData = [
            'startDate' => $booking->getStartDate()->format('Y-m-d H:i:s'),
            'endDate' => $booking->getEndDate()->format('Y-m-d H:i:s'),
            'status' => $booking->getStatus()
        ];

        return new JsonResponse([
            'message' => 'Booking added successfully',
            'booking' => $bookingData
        ], Response::HTTP_CREATED);

        return new JsonResponse(['message' => 'Booking added successfully'], Response::HTTP_CREATED);
    }

    #[Route('/api/bookings/{id}/status', name: 'update_booking_status', methods: ['PATCH'])]
    public function cancelLoan(
        int $id,
        BookingRepository $bookingRepository,
        EntityManagerInterface $entityManager,
        ValidatorInterface $validator
    ): JsonResponse {

        $booking = $bookingRepository->find($id);
        if (!$booking) {
            return new JsonResponse(['message' => 'Booking not found'], Response::HTTP_NOT_FOUND);
        }

        $booking->setStatus('cancelled');

        $errors = $validator->validate($booking);
        if (count($errors) > 0) {
            $errorsString = (string) $errors;

            return new JsonResponse(['message' => $errorsString], Response::HTTP_BAD_REQUEST);
        }

        try {
            $entityManager->persist($booking);
            $entityManager->flush();
        } catch (\Exception $e) {
            return new JsonResponse(['message' => 'An error occurred while updating the booking: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return new JsonResponse(['message' => 'Booking cancelled successfully'], Response::HTTP_OK);
    }
}
