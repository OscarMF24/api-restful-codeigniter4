<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use Faker\Factory;

class UsersSeeder extends Seeder
{
    public function run()
    {
        $admin = [
            'name' => 'Oscar',
            'last_name' => 'People4business',
            'phone' => '5613298400',
            'email' => 'oscar@people4business.com',
            'password' => password_hash('People4business', PASSWORD_BCRYPT),
            'type_user' => 'admin'
        ];

        $user = [
            'name' => 'Jesus',
            'last_name' => 'Franco',
            'phone' => '5613298411',
            'email' => 'jesus@people4business.com',
            'password' => password_hash('Jesus454', PASSWORD_BCRYPT),
        ];

        $this->db->table('users')->insert($admin);
        $this->db->table('users')->insert($user);

        for ($i = 0; $i < 5; $i++) {
            $this->db->table('users')->insert($this->generateBasicUsers());
        }
    }

    /**
     * Generate an array of basic user data.
     *
     * @return array
     */
    private function generateBasicUsers(): array
    {
        $faker = Factory::create();

        return [
            'name' => explode(' ', $faker->name())[0],
            'last_name' => explode(' ', $faker->name())[1],
            'phone' => $faker->numerify('55########'),
            'email' => $faker->freeEmail(),
            'password' => password_hash('password', PASSWORD_DEFAULT),
        ];
    }
}
