<?php

namespace App\Repository;

use App\Entity\Book;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\DBAL\Connection;

/**
 * @extends ServiceEntityRepository<Book>
 *
 * @method Book|null find($id, $lockMode = null, $lockVersion = null)
 * @method Book|null findOneBy(array $criteria, array $orderBy = null)
 * @method Book[]    findAll()
 * @method Book[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BookRepository extends ServiceEntityRepository
{
    private $connection;

    public function __construct(ManagerRegistry $registry, Connection $connection)
    {
        parent::__construct($registry, Book::class);
        $this->connection = $connection;
    }

    /**
     * @return Book[] Returns an array of Book objects based on filters
     */
    public function findByFilters(?string $title, ?string $category, ?string $publishedYear): array
    {
        $sql = 'SELECT * FROM book WHERE 1=1';
        $params = [];

        if ($title) {
            $sql .= ' AND title LIKE :title';
            $params['title'] = '%' . $title . '%';
        }

        if ($category) {
            $sql .= ' AND category = :category';
            $params['category'] = $category;
        }

        if ($publishedYear) {
            $sql .= ' AND EXTRACT(YEAR FROM published_at) = :publishedYear';
            $params['publishedYear'] = $publishedYear;
        }

        $stmt = $this->connection->prepare($sql);
        $result = $stmt->executeQuery($params);

        return $result->fetchAllAssociative();
    }

    //    /**
    //     * @return Book[] Returns an array of Book objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('b')
    //            ->andWhere('b.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('b.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Book
    //    {
    //        return $this->createQueryBuilder('b')
    //            ->andWhere('b.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
