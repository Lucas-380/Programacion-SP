<?php
require_once './models/Usuario.php';
require_once './utils/AutentificadorJWT.php';


class LoginController
{  
    public function Login($request, $response, $args){
        $parametros = $request->getParsedBody();

        $username = $parametros['username'];
        $contrasenia = $parametros['contrasenia'];
        $usuario = Usuario::TraerUsuarioPorSesion($username, $contrasenia);

        if($usuario){ 
            $datos = array('id' => $usuario->id, 'rol'=> $usuario->rol);
            $token = AutentificadorJWT::CrearToken($datos);
            $payload = json_encode(array('jwt' => $token));
        } else {
            $payload = json_encode(array('error' => 'Usuario o contrasenia incorrectos'));
        }

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }
}