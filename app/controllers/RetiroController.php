<?php
require_once './models/Retiro.php';
require_once './controllers/CuentaController.php';
require_once './interfaces/IApiUsable.php';

class RetiroController extends Retiro implements IApiUsable
{
    public function CargarUno($request, $response, $args)
    {
        $parametros = $request->getParsedBody();

        $nroDeCuenta = $parametros['nroDeCuenta'];
        $importe = $parametros['importe'];
        $fecha = (new DateTime('now', new DateTimeZone('America/Argentina/Buenos_Aires')))->format('Y-m-d H:i:s');

        $newRetiro = new Retiro();
        $newRetiro->nroDeCuenta = $nroDeCuenta;
        $newRetiro->monto = $importe;
        $newRetiro->fecha = $fecha;

        //actualizo la el saldo de la cuenta
        $cuenta = Cuenta::obtenerCuenta($nroDeCuenta);
        if(CuentaController::retirarImporte($cuenta, $newRetiro)){
          $newRetiro->crearRetiro();
          $payload = json_encode(array("mensaje" => "Retiro creado con exito"));
        }else{
          $payload = json_encode(array("Error" => "Retiro no se pudo crear - saldo insuficiente o error en datos"));
        }

        $response->getBody()->write($payload);
        return $response;
    }

    public function TraerUno($request, $response, $args)
    {
        $id = $args['id'] ?? null;
        $retiro = Retiro::obtenerRetiro($id);
        $payload = json_encode($retiro);
        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
    }

    public function TraerTodos($request, $response, $args)
    {
        $lista = Retiro::obtenerTodos();
        $payload = json_encode(array("Retiro: " => $lista));
        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
    }

    public function Modificar($request, $response, $args)
    {
      $parametros = $request->getParsedBody();
      $id = $parametros['id'];
      $retiro = Retiro::obtenerRetiro($id);
  
      if($retiro){
        $atributosModificables = ['fecha', 'cuenta', 'monto'];

        foreach ($atributosModificables as $atributoModificado) {
            if(isset($parametros[$atributoModificado])){
                $retiro->{$atributoModificado} = $parametros[$atributoModificado];
            }
        }

        $retiro->modificarRetiro();
        
        $payload = json_encode(array("mensaje" => "Retiro modificado correctamente"));
      } else {
          $payload = json_encode(array("mensaje" => "Error al modificar Retiro"));
      }
  
      $response->getBody()->write($payload);
      return $response->withHeader('Content-Type', 'application/json');
    }

    public function Eliminar($request, $response, $args)
    {
      $payload = json_encode(array("mensaje" => "No es posible eliminar un retiro"));

      $response->getBody()->write($payload);
      return $response->withHeader('Content-Type', 'application/json');
    }


    // - CONSULTAS - 


    public function ConsultarTotalRetirado($request, $response, $args)
    {
      $fecha = $request->getQueryParams('fecha');
      
      if($fecha == null) {
        $ayer = new DateTime('now', new DateTimeZone('America/Argentina/Buenos_Aires'));
        $ayer->modify('-1 day');
        $retiros = Retiro::consultarRetirosEnFecha($ayer->format('Y-m-d'));
      }else{
          $retiros = Retiro::consultarRetirosEnFecha($fecha['fecha']);
      }

      $payload = json_encode($retiros);
      $response->getBody()->write($payload);
      return $response
        ->withHeader('Content-Type', 'application/json');
    }

    public function ConsultarRetirosDeUsuario($request, $response, $args)
    {
      $nroDeCuenta = $request->getQueryParams('nroDeCuenta');
      $retiros = Retiro::obtenerRetirosDeUsuario($nroDeCuenta['nroDeCuenta']);

      if($retiros != null) {
        $payload = json_encode($retiros);
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
      $retiros = Retiro::obtenerRetirosEntreFecha($fechaInicio['fechaInicio'], $fechaFinal['fechaFinal']);

      if($retiros != null) {
        $payload = json_encode($retiros);
      }else{
        $payload = json_encode(array("mensaje" => "Error - No se encontraron retiros"));
      }

      $response->getBody()->write($payload);
      return $response
        ->withHeader('Content-Type', 'application/json');
    }

    public function ConsultarPorTipoDeCuenta($request, $response, $args)
    {
      $tipoDeCuenta = $request->getQueryParams('tipoDeCuenta');
      $retirosTotal = Deposito::obtenerTodos();
      $retirosFiltrado = array();

      foreach ($retirosTotal as $retiro) {
        $cuenta = Cuenta::obtenerCuenta($retiro->nroDeCuenta);
        if($cuenta->tipoDeCuenta === strtoupper($tipoDeCuenta['tipoDeCuenta'])){
          array_push($retirosFiltrado, $retiro);
        }
      }

      if($retirosFiltrado != null) {
        $payload = json_encode($retirosFiltrado);
      }else{
        $payload = json_encode(array("mensaje" => "Error - No se encontraron retiros con ese tipo de cuenta"));
      }

      $response->getBody()->write($payload);
      return $response
        ->withHeader('Content-Type', 'application/json');
    }
}