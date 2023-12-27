<?php
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Psr\Http\Message\ServerRequestInterface as ServerRequest;
use Slim\Psr7\Response;

class ValidarAjusteMW
{
    public function __invoke(Request $request, RequestHandler $handler): Response
    {   
        $parametros = $request->getParsedBody();

        if(array_key_exists('nroDeOperacion', $parametros) && array_key_exists('motivo', $parametros) && array_key_exists('monto', $parametros))
        {
            $nroDeOperacion = $parametros['nroDeOperacion'];
            $motivo = $parametros['motivo'];
            $monto = $parametros['monto'];

            if ($nroDeOperacion != null && $motivo != null && $monto != null){
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