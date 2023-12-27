<?php
require_once './models/Deposito.php';
require_once './models/Cuenta.php';
require_once './controllers/CuentaController.php';
require_once './interfaces/IApiUsable.php';

class DepositoController extends Deposito implements IApiUsable
{
    public function CargarUno($request, $response, $args)
    {
        $parametros = $request->getParsedBody();

        $nroDeCuenta = $parametros['nroDeCuenta'];
        $importe = $parametros['importe'];
        $fecha = (new DateTime('now', new DateTimeZone('America/Argentina/Buenos_Aires')))->format('Y-m-d H:i:s');
        $imagen = $request->getUploadedFiles();

        $cuenta = Cuenta::obtenerCuenta($nroDeCuenta);

        $newDeposito = new Deposito();
        $newDeposito->nroDeCuenta = $nroDeCuenta;
        $newDeposito->monto = $importe;
        $newDeposito->fecha = $fecha;

        //actualizo la el saldo de la cuenta
        if(CuentaController::depositarImporte($cuenta, $newDeposito)){
          $idDeposito = $newDeposito->crearDeposito();
          $depositoCargado = Deposito::obtenerDeposito($idDeposito);
  
          $depositoCargado->imagen = DepositoController::guardarImagen($imagen, $depositoCargado, $cuenta);
          $depositoCargado->modificarDeposito();
  
          $payload = json_encode(array("mensaje" => "Deposito creado con exito"));
        }else{
          $payload = json_encode(array("Error" => "Deposito no se pudo crear"));
        }



        $response->getBody()->write($payload);
        return $response;
    }

    public static function guardarImagen($uploadedFiles, $deposito, $cuenta) 
    {
      if(isset($uploadedFiles['imagen']) && $uploadedFiles['imagen']->getError() === UPLOAD_ERR_OK) {
        $carpetaImg = './ImagenesDeDepositos2023/';
        $nombreImg = $cuenta->tipoDeCuenta.$cuenta->nroDeCuenta."_".$deposito->id;
        $ruta = $carpetaImg . $nombreImg . ".jpg";
  
        if (!is_dir($carpetaImg)) {
            mkdir($carpetaImg, 0777, true);
        }
  
        /** @var UploadedFile $imagen */
        $imagen = $uploadedFiles['imagen'];
        try {
            $imagen->moveTo($ruta);
            $retorno = $ruta;
        } catch (Exception $e) {
          $retorno = false;
        }
      } else {
        $retorno = false;
      }
      return $retorno;
    }

    public function TraerUno($request, $response, $args)
    {
        $id = $args['id'] ?? null;
        $deposito = Deposito::obtenerDeposito($id);
        $payload = json_encode($deposito);
        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
    }

    public function TraerTodos($request, $response, $args)
    {
        $lista = Deposito::obtenerTodos();
        $payload = json_encode(array("Depositos: " => $lista));
        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
    }

    public function Modificar($request, $response, $args)
    {
      $parametros = $request->getParsedBody();
      $id = $parametros['id'];
      $deposito = Deposito::obtenerDeposito($id);
  
      if($deposito){
        $atributosModificables = ['fecha', 'cuenta', 'monto', 'imagen'];

        foreach ($atributosModificables as $atributoModificado) {
            if(isset($parametros[$atributoModificado])){
                $deposito->{$atributoModificado} = $parametros[$atributoModificado];
            }
        }

        $deposito->modificarDeposito();
        
        $payload = json_encode(array("mensaje" => "Deposito modificado correctamente"));
      } else {
          $payload = json_encode(array("mensaje" => "Error al modificar Deposito"));
      }
  
      $response->getBody()->write($payload);
      return $response->withHeader('Content-Type', 'application/json');
    }

    public function Eliminar($request, $response, $args)
    {
      $payload = json_encode(array("mensaje" => "No es posible eliminar un deposito"));

      $response->getBody()->write($payload);
      return $response->withHeader('Content-Type', 'application/json');
    }

    public function ConsultarTotalDepositado($request, $response, $args)
    {
      $fecha = $request->getQueryParams('fecha');
      
      if($fecha == null) {
        $ayer = new DateTime('now', new DateTimeZone('America/Argentina/Buenos_Aires'));
        $ayer->modify('-1 day');
        $depositosEnFecha = Deposito::consultarDepositosEnFecha($ayer->format('Y-m-d'));
      }else{
          $depositosEnFecha = Deposito::consultarDepositosEnFecha($fecha['fecha']);
      }

      $payload = json_encode($depositosEnFecha);
      $response->getBody()->write($payload);
      return $response
        ->withHeader('Content-Type', 'application/json');
    }

    public function ConsultarDepositosDeUsuario($request, $response, $args)
    {
      $nroDeCuenta = $request->getQueryParams('nroDeCuenta');
      $depositos = Deposito::obtenerDepositosDeUsuario($nroDeCuenta['nroDeCuenta']);

      if($depositos != null) {
        $payload = json_encode($depositos);
      }else{
        $payload = json_encode(array("mensaje" => "Error - verifique el nro de cuenta"));
      }

      $response->getBody()->write($payload);
      return $response
        ->withHeader('Content-Type', 'application/json');
    }

    public function ConsultarEntreFechas($request, $response, $args)
    {
      $fechaInicio = $request->getQueryParams('fechaInicio');
      $fechaFinal = $request->getQueryParams('fechaFinal');
      $depositosEntreFechas = Deposito::obtenerDepositosEntreFecha($fechaInicio['fechaInicio'], $fechaFinal['fechaFinal']);

      if($depositosEntreFechas != null) {
        $payload = json_encode($depositosEntreFechas);
      }else{
        $payload = json_encode(array("mensaje" => "Error - No se encontraron depositos"));
      }

      $response->getBody()->write($payload);
      return $response
        ->withHeader('Content-Type', 'application/json');
    }

    public function ConsultarPorTipoDeCuenta($request, $response, $args)
    {
      $tipoDeCuenta = $request->getQueryParams('tipoDeCuenta');
      $depositosTotal = Deposito::obtenerTodos();
      $depositosFiltrado = array();

      foreach ($depositosTotal as $deposito) {
        $cuenta = Cuenta::obtenerCuenta($deposito->nroDeCuenta);
        if($cuenta->tipoDeCuenta === strtoupper($tipoDeCuenta['tipoDeCuenta'])){
          array_push($depositosFiltrado, $deposito);
        }
      }

      if($depositosFiltrado != null) {
        $payload = json_encode($depositosFiltrado);
      }else{
        $payload = json_encode(array("mensaje" => "Error - No se econtro depositos con ese tipo de cuenta"));
      }

      $response->getBody()->write($payload);
      return $response
        ->withHeader('Content-Type', 'application/json');
    }
}