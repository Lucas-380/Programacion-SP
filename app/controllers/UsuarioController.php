<?php
require_once './models/Usuario.php';

class UsuarioController extends Usuario
{
    public function CargarUno($request, $response, $args)
    {
        $parametros = $request->getParsedBody();

        $nombreDeUsuario = $parametros['nombreDeUsuario'];
        $contrasenia = $parametros['contrasenia'];
        $rol = $parametros['rol'];

        $newUsuario = new Usuario();
        $newUsuario->nombreDeUsuario = $nombreDeUsuario;
        $newUsuario->contrasenia = $contrasenia;
        $newUsuario->rol = $rol;

        $newUsuario = $newUsuario->crearUsuario();
        $payload = json_encode(array("mensaje" => "Cuenta creada con exito"));


        $response->getBody()->write($payload);
        return $response;
    }

    public function TraerUno($request, $response, $args)
    {
        $id = $args['id'] ?? null;
        $cuenta = Usuario::obtenerUser($id);
        $payload = json_encode($cuenta);
        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
    }

    public function TraerTodos($request, $response, $args)
    {
        $lista = Usuario::obtenerTodos();
        $payload = json_encode(array("Cuentas" => $lista));
        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
    }
}