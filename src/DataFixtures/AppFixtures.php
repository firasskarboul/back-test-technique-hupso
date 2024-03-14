<?php

namespace App\DataFixtures;

use App\Entity\Book;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');
        for($i=0; $i<10; $i++){
            $book = new Book();

            $book->setTitle(ucfirst($faker->words(3, true)))
                ->setDescription($faker->paragraph(2))
                ->setAuthor($faker->name())
                ->setCategory($faker->word())
                ->setPublishedAt(\DateTimeImmutable::createFromMutable($faker->dateTime()));

            $manager->persist($book);
        }

        $manager->flush();
    }
}
