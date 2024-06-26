<?php

namespace App\Repository;

use App\Entity\Posts;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Posts>
 *
 * @method Posts|null find($id, $lockMode = null, $lockVersion = null)
 * @method Posts|null findOneBy(array $criteria, array $orderBy = null)
 * @method Posts[]    findAll()
 * @method Posts[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PostsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Posts::class);
    }
    // function pour récupérer des datas de la base de données, équivalent à une requête SQL.
    public function findByTitle(string $title): array
    {
       return $this->createQueryBuilder('p')
             ->where('p.title = :title')
            //  ->orderBy('p.publishedAt', 'DESC')
             ->setParameter('title', $title)
             ->getQuery()   
             ->getResult();
    }
    
}
