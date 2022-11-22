<?php

namespace Tests;

use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;
use Faker\Factory as Faker;

class ExampleTest extends TestCase
{
    /*
     * A basic test example.
     *
     * @return void
    public function test_that_base_endpoint_returns_a_successful_response()
    {
        //$user = $this->registerUser();
                
        $login_data = [
            'email' => 'test@test.com',
            'password' => 'testtest',            
        ];
        
        $response = $this->post("/api/login", $login_data)
            ->seeJson();
    }
    
    public function registerUser()
    {
        $faker = Faker::create();
                
        $register_data = [
            'name' => $faker->name(),
            'email' => $faker->email(),
            'password' => 'testtest',            
        ];
        
        $response = $this->post("/api/register", $register_data)
             ->seeJson([
                 'code' => 200,
             ]);

        return $register_data;        
    }
    
         */

}
