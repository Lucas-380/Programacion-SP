<?php
require_once './models/Ajuste.php';
require_once './controllers/DepositoController.php';
require_once './controllers/RetiroController.php';
require_once './controllers/CuentaController.php';
require_once './interfaces/IApiUsable.php';

class AjusteController extends Ajuste implements IApiUsable
{
    public function CargarUno($request, $response, $args)
    {
        $parametros = $request->getParsedBody();

        $nroDeOperacion = $parametros['nroDeOperacion'];
        $motivo = $parametros['motivo'];
        $monto = $parametros['monto'];
        
        $newAjuste = new Ajuste();
        $newAjuste->nroDeOperacion = $nroDeOperacion;
        $newAjuste->motivo = $motivo;
        $newAjuste->monto = $monto;

        //buscarOperacion
        $operacion = AjusteController::buscarOperacion($nroDeOperacion);
        
        //actualizo la el saldo de la cuenta
        if($operacion != null)
        {
          $newAjuste->operacion = get_class($operacion);
          $cuenta = Cuenta::obtenerCuenta($operacion->nroDeCuenta);
          if($cuenta && CuentaController::depositarImporte($cuenta, $newAjuste))
          {
            $newAjuste->crearAjuste();
            $payload = json_encode(array("mensaje" => "Ajuste realizado con exito"));
          }
        }else{
          $payload = json_encode(array("Error" => "Ajuste no se pudo realizar - saldo insuficiente o error en datos"));
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


    private static function buscarOperacion($nroDeOperacion){
      $operacion = Deposito::obtenerDeposito($nroDeOperacion);
      if($operacion === false || $operacion === null)
      {
        $operacion = Retiro::obtenerRetiro($nroDeOperacion);
        if($operacion === false || $operacion === null){
          return null;
        }
      }
      return $operacion;
    }
}