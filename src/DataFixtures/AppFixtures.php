<?php

namespace App\DataFixtures;

use App\Entity\User;
use App\Entity\Movie;
use App\Entity\Vote;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * AppFixtures
 * This class is responsible for loading all initial data (users, movies, votes)
 * into the database for the MovieWorld application.
 * It combines the logic from UserFixtures, MovieFixtures, and VoteFixtures
 * to ensure proper data relationships and constraints.
 */
class AppFixtures extends Fixture
{
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create();

        // Create Users
        $users = [];
        $userCount = 100;
        echo "Creating {$userCount} users...\n";

        for ($i = 0; $i < $userCount; $i++) {
            $user = new User();
            $user->setUsername($faker->userName);
            $user->setName($faker->name);
            $user->setRoles([]);
            $user->setEmail($faker->unique()->safeEmail);
            $user->setPassword($this->passwordHasher->hashPassword($user, 'password'));
            $manager->persist($user);
            $users[] = $user;
        }

        $manager->flush();
        echo "Users created.\n";

        // Create Movies
        $movies = [];
        $movieCount = 250;
        echo "Creating {$movieCount} movies...\n";
        for ($i = 0; $i < $movieCount; $i++) {
            $movie = new Movie();
            $movie->disableAutoSetCreatedAt();
            $movie->setTitle($faker->sentence(mt_rand(2, 5)));
            $movie->setDescription($faker->paragraph(mt_rand(3, 7)));
            $movie->setCreatedAt(\DateTimeImmutable::createFromMutable($faker->dateTimeBetween('-5 years', 'now')));

            $randomUser = $faker->randomElement($users);
            $movie->setUser($randomUser);

            $manager->persist($movie);
            $movies[] = $movie;
        }
        $manager->flush();
        echo "Movies created.\n";

        // Create Votes
        $voteCount = 5000;
        $voteTypes = [Vote::TYPE_LIKE, Vote::TYPE_HATE];
        $votedCombinations = []; // To track unique user-movie votes
        echo "Creating {$voteCount} votes...\n";

        for ($i = 0; $i < $voteCount; $i++) {
            $randomUser = $faker->randomElement($users);
            $randomMovie = $faker->randomElement($movies);

            // Ensure user does not vote on their own movie
            // And ensure user has not already voted on this movie
            $maxAttempts = 20; // Increased attempts for more robust combination finding
            $attempts = 0;
            while (
                $randomUser->getId() === $randomMovie->getUser()->getId() ||
                isset($votedCombinations[$randomUser->getId()][$randomMovie->getId()])
            ) {
                $randomUser = $faker->randomElement($users);
                $randomMovie = $faker->randomElement($movies);
                $attempts++;
                if ($attempts >= $maxAttempts) {
                    // If we can't find a valid combination after several attempts,
                    // we might skip this vote to prevent an infinite loop.
                    // This can happen if most combinations are already used or rules are too strict.
                    echo "Warning: Could not find a unique user-movie combination for vote {$i} after {$maxAttempts} attempts. Skipping this vote.\n";
                    continue 2; // Skip to the next iteration of the main loop
                }
            }

            $vote = new Vote();
            $vote->setUser($randomUser);
            $vote->setMovie($randomMovie);
            $vote->setType($faker->randomElement($voteTypes));

            $manager->persist($vote);

            // Mark this combination as voted
            $votedCombinations[$randomUser->getId()][$randomMovie->getId()] = true;
        }

        $manager->flush();
        echo "Votes created.\n";
        echo "All fixtures loaded successfully!\n";
    }
}

