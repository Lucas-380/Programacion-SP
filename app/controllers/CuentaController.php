<?php
require_once './models/Cuenta.php';
require_once './controllers/DepositoController.php';
require_once './controllers/RetiroController.php';
require_once './interfaces/IApiUsable.php';

class CuentaController extends Cuenta implements IApiUsable
{
    public function CargarUno($request, $response, $args)
    {
        $parametros = $request->getParsedBody();

        $nombre = $parametros['nombre'];
        $apellido = $parametros['apellido'];
        $tipoDocumento = $parametros['tipoDocumento'];
        $nroDocumento = $parametros['nroDocumento'];
        $tipoDeCuenta = $parametros['tipoDeCuenta'];
        $email = $parametros['email'];
        if(isset($parametros['saldo'])){
          $saldo = $parametros['saldo'];
        }else{
          $saldo = 0;
        }
        $imagen = $request->getUploadedFiles();

        $newCuenta = new Cuenta();
        $newCuenta->nombre = $nombre;
        $newCuenta->apellido = $apellido;
        $newCuenta->tipoDocumento = strtoupper($tipoDocumento);
        $newCuenta->nroDocumento = $nroDocumento;
        $newCuenta->email = $email;
        $newCuenta->saldo = $saldo;
        $newCuenta->tipoDeCuenta = strtoupper($tipoDeCuenta);

        if(Cuenta::validarCuenta($newCuenta)) {
          $nroDeCuenta = $newCuenta->crearCuenta();
          $cuentaCargada = Cuenta::obtenerCuenta($nroDeCuenta);
  
          $cuentaCargada->imagen = CuentaController::guardarImagen($imagen, $cuentaCargada, './ImagenesDeCuentas/2023/');
          $cuentaCargada->modificarCuenta();
  
          $payload = json_encode(array("mensaje" => "Cuenta creada con exito"));
        }else{
          $cuentaExistente = Cuenta::obtenerCuentaPorDocumento($nroDocumento);
          if($cuentaExistente && $saldo > 0){
            $cuentaExistente->saldo = $saldo;
            $cuentaExistente->modificarCuenta();
            $payload = json_encode(array("mensaje" => "Cuenta existente - saldo actualizado"));
          }else{
            $payload = json_encode(array("mensaje" => "Error al crear cuenta - Datos invalidos o cuenta ya existente"));
          }
        }


        $response->getBody()->write($payload);
        return $response;
    }

    public static function guardarImagen($uploadedFiles, $cuenta, $ruta) {
      if(isset($uploadedFiles['imagen']) && $uploadedFiles['imagen']->getError() === UPLOAD_ERR_OK) {
        $carpetaImg = $ruta;
        $nombreImg = $cuenta->nroDeCuenta."_".$cuenta->tipoDeCuenta;
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
        $nroCuenta = $args['nroDeCuenta'] ?? null;
        $cuenta = Cuenta::obtenerCuenta($nroCuenta);
        $payload = json_encode($cuenta);
        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
    }

    public function TraerTodos($request, $response, $args)
    {
        $lista = Cuenta::obtenerTodos();
        $payload = json_encode(array("Cuentas" => $lista));
        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
    }

    public function Modificar($request, $response, $args)
    {
      $parametros = $request->getParsedBody();
      $nroDeCuenta = $parametros['nroDeCuenta'];
      $tipoDeCuenta = $parametros['tipoDeCuenta'];
      $cuenta = Cuenta::obtenerCuenta($nroDeCuenta);
  
      if($cuenta && $cuenta->tipoDeCuenta === strtoupper($tipoDeCuenta)){
        $atributosModificables = ['nombre', 'apellido', 'tipoDocumento', 'nroDocumento', 'email', 'fechaDeBaja'];

        foreach ($atributosModificables as $atributoModificado) {
            if(isset($parametros[$atributoModificado])){
                if($atributoModificado === 'tipoDocumento' || $atributoModificado === 'tipoDeCuenta'){
                  $cuenta->{$atributoModificado} = strtoupper($parametros[$atributoModificado]);  
                }else{
                  $cuenta->{$atributoModificado} = $parametros[$atributoModificado];  
                }
            }
        }

        $cuenta->modificarCuenta();
        
        $payload = json_encode(array("mensaje" => "Cuenta modificada correctamente"));
      } else {
          $payload = json_encode(array("mensaje" => "Error al modificar cuenta"));
      }
  
      $response->getBody()->write($payload);
      return $response->withHeader('Content-Type', 'application/json');
    }

    public function Eliminar($request, $response, $args)
    {
      $parametros = $request->getParsedBody();
      $tipoDeCuenta = $parametros['tipoDeCuenta'];
      $nroDeCuenta = $parametros['nroDeCuenta'];
      $cuenta = Cuenta::obtenerCuenta($nroDeCuenta);

      if($cuenta && $cuenta->tipoDeCuenta === strtoupper($tipoDeCuenta) && Cuenta::borrarCuenta($cuenta->nroDeCuenta)){
        $payload = json_encode(array("mensaje" => "Cuenta eliminado con exito"));
      }else{
        $payload = json_encode(array("mensaje" => "Error en eliminar cuenta"));
      }

      $response->getBody()->write($payload);
      return $response->withHeader('Content-Type', 'application/json');
    }

    public static function depositarImporte($cuenta, $deposito){
      if($cuenta != null && $deposito != null){
        $cuenta->saldo += $deposito->monto;

        if($cuenta->saldo >= 0){
          $cuenta->modificarCuenta();
          return true;
        }
      }
      return false;
    }

    public static function retirarImporte($cuenta, $deposito){
      if($cuenta != null && $deposito != null){
        $cuenta->saldo -= $deposito->monto;
        if($cuenta->saldo >= 0){
          $cuenta->modificarCuenta();
          return true;
        }
      }
      return false;
    }

    //listados
    public function ConsultarCuenta($request, $response, $args){
      $parametros = $request->getParsedBody();
      $tipoDeCuenta = strtoupper($parametros['tipoDeCuenta']);
      $nroDeCuenta = $parametros['nroDeCuenta'];

      $cuenta = Cuenta::obtenerCuenta($nroDeCuenta);

      if($cuenta){
        if($cuenta->tipoDeCuenta === $tipoDeCuenta){
          $payload = json_encode(array("Tipo de cuenta y moneda:" => "$cuenta->tipoDeCuenta", "Saldo:" => "$cuenta->saldo"));
        }else{
          $payload = json_encode(array("mensaje" => "Existe el nro de cuenta pero no coincide con el tipo"));
        }
      }else{
        $payload = json_encode(array("mensaje" => "El nro de cuenta no existe"));
      }

      $response->getBody()->write($payload);
      return $response->withHeader('Content-Type', 'application/json');
    }

    public function ConsultarOperaciones($request, $response, $args)
    {
      $nroCuenta = $args['nroDeCuenta'] ?? null;
      $cuenta = Cuenta::obtenerCuenta($nroCuenta);
      $operaciones = array();

      if($cuenta){
        $depositos = Deposito::obtenerDepositosDeUsuario($cuenta->nroDeCuenta);
        $retiros = Retiro::obtenerRetirosDeUsuario($cuenta->nroDeCuenta);
        $payload = json_encode(array("Depositos" => $depositos, "Retiros" => $retiros));
      }else{
        $payload = json_encode(array("mensaje" => "La cuenta no existe"));
      }

      $response->getBody()->write($payload);
      return $response
        ->withHeader('Content-Type', 'application/json');

    }
  }