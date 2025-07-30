<?php

namespace App\Repository;

use App\Entity\Movie;
use App\Entity\User;
use App\Entity\Vote;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Vote>
 */
class VoteRepository extends ServiceEntityRepository
{
    private EntityManagerInterface $em;

    public function __construct(ManagerRegistry $registry, EntityManagerInterface $em)
    {
        parent::__construct($registry, Vote::class);
        $this->em = $em;
    }

    /**
     * Handles voting logic for a user and movie.
     *
     * @param User $user
     * @param Movie $movie
     * @param string $newType
     */
    public function vote(User $user, Movie $movie, string $newType): void
    {
        $existingVote = $this->findOneBy(['user' => $user, 'movie' => $movie]);

        if ($existingVote) {
            if ($existingVote->getType() == $newType) {
                $this->em->remove($existingVote);
            } else {
                $existingVote->setType($newType);
            }
        } else {
            $vote = new Vote();
            $vote->setUser($user);
            $vote->setMovie($movie);
            $vote->setType($newType);
            $this->em->persist($vote);
        }

        $this->em->flush();
    }

    /**
     * Returns count of votes by type
     * @param Movie $movie
     * @param string $type
     * @return int
     */
    public function countVotesByType(Movie $movie, string $type): int
    {
        return $this->count(['movie' => $movie, 'type' => $type]);
    }

    /**
     * Returns user vote type
     * @param User $user
     * @param Movie $movie
     * @return string|null
     */
    public function getUserVoteType(User $user, Movie $movie): ?string
    {
        $vote = $this->findOneBy(['user' => $user, 'movie' => $movie]);
        return $vote ? $vote->getType() : null;
    }
}
