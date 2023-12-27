<?php
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Psr\Http\Message\ServerRequestInterface as ServerRequest;
use Slim\Psr7\Response;

class ValidarRetiroMW
{
    public function __invoke(Request $request, RequestHandler $handler): Response
    {   
        $parametros = $request->getParsedBody();

        if(array_key_exists('tipoDeCuenta', $parametros) && array_key_exists('nroDeCuenta', $parametros) && array_key_exists('importe', $parametros))
        {
            $tipoDeCuenta = $parametros['tipoDeCuenta'];
            $nroDeCuenta = $parametros['nroDeCuenta'];
            $importe = $parametros['importe'];

            $cuenta = Cuenta::obtenerCuenta($nroDeCuenta);
            if ($cuenta && $cuenta->tipoDeCuenta === strtoupper($tipoDeCuenta) && $importe != null && $importe > 0){
                $response = $handler->handle($request);
            }else{
                $response = new Response();
                $payload = json_encode(array("mensaje" => "Error - Los datos son incorrecto"));
                $response->getBody()->write($payload); 
            }
        }else {
            $response = new Response();
            $payload = json_encode(array("mensaje" => "Error - Faltan datos"));
            $response->getBody()->write($payload);
        }

        return $response->withHeader('Content-Type', 'application/json');
    }
}