<?php
namespace Tests\Feature;

use App\Customer;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class CustomerTest extends TestCase
{

    use RefreshDatabase;
    use DatabaseMigrations;

    /**
     * A basic test example.
     *
     * @return void
     */

    public function testSaveCustomer()
    {

        /*
        Este test comprueba que se pueda hacer la llamada a la url "api/saveCustomer"
        y que a la misma se le envíe un parámetro "input" con un objeto en JSON.
        Si el status de respuesta de la llamada a la url es 200
        y la respuesta está en el formato JSON definido el test se considera satisfactorio.
        */

        $response = $this->withHeaders([
            'X-Header' => 'Value',
        ])->json('POST', 'api/saveCustomer', ['input' => '{"id":"500","name":"Berny Ventura"}'])
            ->assertStatus(200)
            ->assertJson([
                'status'  => 'success',
                'code'    => '200',
                'message' => 'The customer has been created succesfully',
            ]);
    }

    public function testGetAllCustomers(){

        /*
        Este test comprueba que se pueda hacer la llamada a la url "api/getCustomers".
        Si el status de respuesta de la llamada a la url es 200
        y la respuesta está en el formato JSON definido el test se considera satisfactorio.
        */

        $response = $this->json('GET', '/api/getCustomers', [], [])
            ->assertStatus(200)
            ->assertJson([
                'status'  => 'success',
                'code'    => '200'
            ]);
    }

    public function _testGetOneCustomer(){

        /*
        Este test comprueba que se pueda hacer la llamada a la url "api/getCustomers/{id}".
        Si el status de respuesta de la llamada a la url es 200
        y la respuesta está en el formato JSON definido el test se considera satisfactorio.
        */

            $customerID =  Customer::pluck('id')->first();

            if(!isset($customerID))
                return false;
            

            $url = '/api/getCustomers/'.$customerID;

            $response = $this->json('GET', $url, [], [])
            ->assertStatus(200)
            ->assertJson([
                'status'  => 'success',
                'code'    => '200'
            ]);
    }

    public function _testXMLvsXSD(){

        /*
        Este test comprueba que se pueda hacer la llamada a la url "api/validarXML".
        Si el status de respuesta de la llamada a la url es 200
        y la respuesta está en el formato JSON definido el test se considera satisfactorio.
        */
         
         $response = $this->json('GET', '/api/validarXML', [])
            ->assertStatus(200)
            ->assertJson([
                'status'   => 'success',
                'code'    => '200'
            ]);
    }

     
}
