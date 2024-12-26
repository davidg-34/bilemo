<?php

namespace App\DataFixtures;

use App\Entity\User;
use App\Entity\Customer;
use App\Entity\Phone;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        // Creation des Clients
        $clients = [];
        $clientNames = [
            'Fnac', 'Boulanger', 'ElectroDepot', 'Darty', 'FreeMobile',
            'MicroMania', 'Amazon', 'Orange', 'Buygues', 'SFR'
        ];

        foreach ($clientNames as $index => $clientName) {
            $customer = new Customer();
            $customer->setName($clientName)
                     ->setEmail(strtolower($clientName) . "@example.com")
                     ->setRoles(["ROLE_ADMIN"]);
            $customer->setPassword($this->passwordHasher->hashPassword($customer, 'pass_123'));

            $manager->persist($customer);
            $clients[] = $customer;
        }

        // Creation des Users
        $userNames = [
            ['John', 'Doe'], ['Jane', 'Smith'], ['Alice', 'Cordier'], ['Pascal', 'Leroy'], ['Charles', 'Gole'],
            ['Diane', 'Poitier'], ['Eve', 'Legris'], ['Jérôme', 'Verkiel'], ['Louise', 'Lavalière'], ['Antoine', 'Devers'],
            ['Henri', 'James'], ['Marie', 'Capet'], ['Laure', 'Noize'], ['Leo', 'Dugaut'], ['Mila', 'Lafont']
        ];

        foreach ($userNames as $name) {
            $user = new User();
            [$firstName, $lastName] = $name;
            $email = strtolower($firstName . '.' . $lastName) . "@example.com";
            $user->setFirstName($firstName)
                 ->setLastName($lastName)
                 ->setEmail($email);

            // Liaison des utilisateurs à un client random
            $randomClient = $clients[array_rand($clients)];
            $randomClient->addUser($user);

            $manager->persist($user);
        }

        // Creation des Phones
        $phonesData = [
            ['brand' => 'Apple', 'model' => 'iPhone 14', 'description' => 'Latest Apple smartphone.', 'price' => 999.99],
            ['brand' => 'Samsung', 'model' => 'Galaxy S23', 'description' => 'Flagship Samsung device.', 'price' => 899.99],
            ['brand' => 'Google', 'model' => 'Pixel 7', 'description' => 'Google\'s latest phone.', 'price' => 799.99],
            ['brand' => 'OnePlus', 'model' => 'OnePlus 11', 'description' => 'High-performance phone.', 'price' => 749.99],
            ['brand' => 'Xiaomi', 'model' => 'Mi 13', 'description' => 'Affordable and powerful.', 'price' => 699.99],
            ['brand' => 'Sony', 'model' => 'Xperia 5', 'description' => 'Compact and feature-rich.', 'price' => 649.99],
            ['brand' => 'Huawei', 'model' => 'P60 Pro', 'description' => 'Premium phone with great camera.', 'price' => 899.99],
            ['brand' => 'Motorola', 'model' => 'Edge 40', 'description' => 'Stylish and durable.', 'price' => 599.99],
            ['brand' => 'Nokia', 'model' => 'G22', 'description' => 'Budget-friendly smartphone.', 'price' => 299.99],
            ['brand' => 'Asus', 'model' => 'ROG Phone 6', 'description' => 'Gaming powerhouse.', 'price' => 999.99],
            ['brand' => 'Apple', 'model' => 'iPhone SE', 'description' => 'Compact and powerful.', 'price' => 429.99],
            ['brand' => 'Samsung', 'model' => 'Galaxy A54', 'description' => 'Mid-range champion.', 'price' => 349.99],
            ['brand' => 'Google', 'model' => 'Pixel 6a', 'description' => 'Affordable Google phone.', 'price' => 449.99],
            ['brand' => 'OnePlus', 'model' => 'Nord CE 3', 'description' => 'Mid-range with great features.', 'price' => 399.99],
            ['brand' => 'Xiaomi', 'model' => 'Redmi Note 12', 'description' => 'Value-packed device.', 'price' => 199.99],
            ['brand' => 'Sony', 'model' => 'Xperia 10', 'description' => 'Affordable Sony phone.', 'price' => 379.99],
            ['brand' => 'Huawei', 'model' => 'Nova 11', 'description' => 'Stylish and affordable.', 'price' => 499.99],
            ['brand' => 'Motorola', 'model' => 'Moto G Stylus', 'description' => 'With stylus support.', 'price' => 249.99],
            ['brand' => 'Nokia', 'model' => 'C32', 'description' => 'Entry-level smartphone.', 'price' => 149.99],
            ['brand' => 'Asus', 'model' => 'Zenfone 9', 'description' => 'Compact flagship.', 'price' => 699.99],
        ];

        foreach ($phonesData as $data) {
            $phone = new Phone();
            $phone->setBrand($data['brand'])
                  ->setModel($data['model'])
                  ->setDescription($data['description'])
                  ->setPrice($data['price']);

            $manager->persist($phone);
        }

        $manager->flush();
    }
}
