<?php

namespace App\Controller;

use App\Entity\Movie;
use App\Entity\Vote;
use App\Repository\VoteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

final class VoteController extends AbstractController
{
    #[Route('/vote/{id}/{type}', name: 'app_vote', methods: ['POST'])]
    public function vote(Movie $movie, string $type, VoteRepository $voteRepo, EntityManagerInterface $em): JsonResponse {
        $user = $this->getUser();
        $validTypes = Vote::getTypes();

        if (!array_key_exists($type, $validTypes)) {
            return new JsonResponse(['error' => 'Invalid vote type.'], 400);
        }

        $existingVote = $voteRepo->findOneBy([
            'user' => $user,
            'movie' => $movie,
        ]);

        $newType = $validTypes[$type];

        if ($existingVote) {
            if ($existingVote->getType() === $newType) {
                $em->remove($existingVote);
            } else {
                // Switch to opposite vote
                $existingVote->setType($newType);
            }
        } else {
            $vote = new Vote();
            $vote->setUser($user);
            $vote->setMovie($movie);
            $vote->setType($newType);
            $em->persist($vote);
        }

        $em->flush();

        return new JsonResponse([
            'likes' => $movie->getVotesByType(Vote::TYPE_LIKE),
            'hates' => $movie->getVotesByType(Vote::TYPE_HATE),
        ]);
    }
}
