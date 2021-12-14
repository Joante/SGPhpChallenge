<?php

namespace App\DataFixtures;

use App\Entity\Catalog;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class CatalogFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $catalog = new Catalog();
        
        $catalog->setFilePath('successSmall.json');
        $catalog->setState('submitted');

        $manager->persist($catalog);

        $catalog = new Catalog();

        $catalog->setFilePath('successLong.json');
        $catalog->setState('submitted');
        
        $manager->persist($catalog);

        $manager->flush();
    }
}
