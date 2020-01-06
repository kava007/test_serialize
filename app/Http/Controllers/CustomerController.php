<?php
namespace App\Http\Controllers;

$autoloader = require base_path()."/vendor/autoload.php";

use Doctrine\Common\Annotations\AnnotationRegistry;
AnnotationRegistry::registerLoader(array($autoloader, "loadClass"));

use App\Customer;
use App\User;
use Illuminate\Http\Request;
use JMS\Serializer\Annotation\XmlAttributeMap;
use JMS\Serializer\Annotation\XmlRoot;
use JMS\Serializer\Annotation\XmlElement;
use JMS\Serializer\Annotation\XmlNamespace;

require base_path()."/vendor/autoload.php";
use XmlValidator\XmlValidator;
use XmlValidator\XsdSource;

class CustomerController extends Controller
{
    
    public function index()
    {
        /*
        Lista todos los empleados de la tabla "customers" y los devuelve en un objeto JSON
        */
        $customers      = Customer::all();
        $totalCustomers = $customers->count();

        if($totalCustomers > 0){
           $data = array(
                      'status'    => 'success',
                      'code'      => '200',
                      'message'   => 'The customers has been retrieved succesfully',
                      'customers' => $customers
                  );
        }

        if($totalCustomers == 0){
           $data = array(
                      'status'    => 'success',
                      'code'      => '200',
                      'message'   => 'There are no customers in the database.',
                      'customers' => $customers
                  );
        }

        return response()->json($data, $data['code']);
    }

    
    public function store(Request $request)
    {
        
        /*
          Recoger los datos del usuario por POST y los guarda en la base de datos.
        */
        $json         = $request->input('input',null);
        $params       = json_decode($json);
        $params_array = json_decode($json, true);
        $params_array = array_map('trim', $params_array);

        if(!empty($params) && !empty($params_array)){
        
            //Validar datos
            $validate = \Validator::make($params_array,[
                'id'      => 'required|unique:customers', //Comprobar si el customer ya existe
                'name'    => 'required',
            ]);

            if($validate->fails()){

                $data = array(
                    'status'  => 'error',
                    'code'    => '404',
                    'message' => 'The customer has not been created.',
                    'errors'  => $validate->errors()
                );

            }else{

                //Crear el customer
                $customer        = new Customer();
                $customer->id    = $params_array["id"];
                $customer->name  = $params_array["name"];
                $customer->save();

                $data = array(
                    'status'  => 'success',
                    'code'    => '200',
                    'message' => 'The customer has been created succesfully',
                    'customer'    => $customer
                );
            }

        }else{

            $data = array(
                    'status'  => 'error',
                    'code'    => '404',
                    'message' => 'Data is incorrect.',
                );

        }

        return response()->json($data, $data['code']);
    }

    public function show($id)
    {
        /*Por medio del id del customer lo busca en la base de datos y en caso de encontrarlo, lo devuelve en un XML que cumple con el XSD de Digibox*/
        $customer = Customer::findOrFail($id);
        $company  = trim($customer->company);

        /*Las clases CampoAdicional y CamposAdicionales fueron creadas para serializar la salida que se espera. */ 
        $x = new CampoAdicional("company",$company);  
        $y = new CamposAdicionales($x);  

        $serializer = \JMS\Serializer\SerializerBuilder::create()
                        ->setPropertyNamingStrategy(
                        new \JMS\Serializer\Naming\SerializedNameAnnotationStrategy(
                        new \JMS\Serializer\Naming\IdenticalPropertyNamingStrategy()))
                        ->build();
        $result = $serializer->serialize($y, 'xml'); 

        return response($result)->header('Content-Type', 'application/xml');   
    }

    public function validarXML(){

        /*Este método comprueba la validez del XML generado contra el XSD de Digibox.
          Para ello obtiene el id del primer registro guardado en la tabla customers y lo valida contra el xsd.
          Método creado para tener certeza que el método "show" devuelve un
          XML válido.
        */ 

        $customerID =  Customer::pluck('id')->first();

        if(!isset($customerID)){

            $data = array(
                    'status'  => 'error',
                    'code'    => '400',
                    'message' => 'No hay registros en la BD.',
            );

            return response()->json($data, $data['code']);
        }


        $url_base  =  'http://'. $_SERVER['SERVER_NAME'].':'.$_SERVER['SERVER_PORT'].dirname($_SERVER['PHP_SELF']);

        $url_api =  $url_base.'/api/getCustomer/'.$customerID;

        $xml = file_get_contents($url_api);
        $xsd = base_path()."/xsd/campos.xsd";

        $xmlValidator = new XmlValidator($xml, $xsd, XsdSource::FILE);

        try{
          
            $xmlValidator->validate($xml,$xsd);

            // Check if is valid
            if(!$xmlValidator->isValid()){

                $data = array(
                    'status'  => 'error',
                    'code'    => '400',
                    'message' => 'XML invalido.',
                );
                
                foreach ($xmlValidator->errors as $error) {
                    echo sprintf('[%s %s] %s (in %s - line %d, column %d)',
                        $error->level, $error->code, $error->message, 
                        $error->file, $error->line, $error->column
                    ); 
                }
            }else{
                //echo "El archivo XML es válido.";

                $data = array(
                    'status'  => 'success',
                    'code'    => '200',
                    'message' => 'XML valido.',
                );

            }
        } catch (\InvalidArgumentException $e){
             //catch InvalidArgumentException
           // echo $e
            echo "ERROR".$e;

        }

        return response()->json($data, $data['code']);
    }

}//end class CustomerController


/*
Las clases "CampoAdicional" y "CamposAdicionales" fueron creadas para
generar la salida en el formato del XSD.
Cada elemento de la clase tiene anotaciones
*/

/** @XmlRoot("CampoAdicional") */
class CampoAdicional{

     /** @XmlAttributeMap */
     private $id = array(
        'nombre' => '',
        'valor'  => '',
     );


     public function __construct($nombre, $valor=''){
          $this->id['nombre'] = $nombre;
          $this->id['valor'] = $valor;
     }
}

/**
 * @XmlNamespace(uri="http://www.digibox.com.mx/cfdi/camposadicionales")
 * @XmlRoot("CamposAdicionales") 
 */
class CamposAdicionales
{

     /**
     *  @XmlElement  "CampoAdicional" (cdata=false)
     */
     private $CampoAdicional = '';

     
     public function __construct($campoadicional){

          $this->CampoAdicional = $campoadicional;
     }
}

