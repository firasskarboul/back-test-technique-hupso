<?php

namespace App\Repository;

use App\Entity\Book;
use App\Entity\Booking;
use App\Entity\Utilisateur;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Booking>
 *
 * @method Booking|null find($id, $lockMode = null, $lockVersion = null)
 * @method Booking|null findOneBy(array $criteria, array $orderBy = null)
 * @method Booking[]    findAll()
 * @method Booking[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BookingRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Booking::class);
    }

    // Add custom query methods if needed

    /**
     * @return Booking[] Returns an array of Booking objects for a given user and status
     */
    public function findActiveByUser(Utilisateur $user): array
    {
        return $this->createQueryBuilder('b')
            ->andWhere('b.user = :user')
            ->andWhere('b.status = :status')
            ->setParameter('user', $user)
            ->setParameter('status', 'active')
            ->getQuery()
            ->getResult();
    }

    /**
     * Check if a book is available for the requested period
     */
    public function isBookAvailable(Book $book, \DateTimeInterface $startDate, \DateTimeInterface $endDate): bool
    {
        $qb = $this->createQueryBuilder('b')
            ->where('b.book = :book')
            ->andWhere('b.status = :status')
            ->andWhere('b.startDate <= :endDate')
            ->andWhere('b.endDate >= :startDate')
            ->setParameter('book', $book)
            ->setParameter('status', 'active')
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate);

        $result = $qb->getQuery()->getResult();

        return count($result) === 0;
    }

}