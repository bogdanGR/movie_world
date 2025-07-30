<?php

namespace App\Controller;

use App\Entity\Movie;
use App\Entity\Vote;
use App\Repository\VoteRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

final class VoteController extends AbstractController
{
    #[Route('/vote/{id}/{type}', name: 'app_vote', methods: ['POST'])]
    public function vote(Movie $movie, string $type, VoteRepository $voteRepo): JsonResponse
    {
        try {
            $user = $this->getUser();
            $validTypes = Vote::getTypes();

            if (!array_key_exists($type, $validTypes)) {
                return new JsonResponse(['error' => 'Invalid vote type.'], 400);
            }

            $newType = $validTypes[$type];

            $voteRepo->vote($user, $movie, $newType);

            $likesCount = $voteRepo->countVotesByType($movie, Vote::TYPE_LIKE);
            $hatesCount = $voteRepo->countVotesByType($movie, Vote::TYPE_HATE);

            $currentUserVoteType = $voteRepo->getUserVoteType($user, $movie);
            $userVote = $currentUserVoteType ? array_search($currentUserVoteType, $validTypes) : null;

            return $this->json([
                'likes' => $likesCount,
                'hates' => $hatesCount,
                'userVote' => $userVote,
            ]);
        } catch (\Throwable $e) {
            return new JsonResponse(['error' => 'Exception: ' . $e->getMessage()], 500);
        }
    }
}
