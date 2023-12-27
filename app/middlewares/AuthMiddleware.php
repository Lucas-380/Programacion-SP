<?php
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Psr\Http\Message\ServerRequestInterface as ServerRequest;
use Slim\Psr7\Response;

class AuthMiddleware
{
    private $rol;
    
    public function __construct($rol) {
        $this->rol = $rol;
    }

    public function __invoke(Request $request, RequestHandler $handler): Response
    {   
        try {
            $header = $request->getHeaderLine('Authorization');
            if($header == ""){
                throw new Exception();
            }
            $token = trim(explode("Bearer", $header)[1]);


            if(AutentificadorJWT::VerificarRol($token, $this->rol)){
                $idUsuario = AutentificadorJWT::ObtenerData($token)->id;
                $request = $request->withAttribute('idUsuario', $idUsuario);
                $response = $handler->handle($request);
            }else{
                $response = new Response();
                $payload = json_encode(array('mensaje' => 'ERROR: Solo disponible para los usuarios del rol: '.$this->rol));
                $response->getBody()->write($payload);
            }
        } catch (Exception $e) {
            $response = new Response();
            $payload = json_encode(array('mensaje' => 'ERROR: Hubo un error con el TOKEN'));
            $response->getBody()->write($payload);
        }
        

        return $response->withHeader('Content-Type', 'application/json');
    }
}