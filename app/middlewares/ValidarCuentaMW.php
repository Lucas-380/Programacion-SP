<?php
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Psr\Http\Message\ServerRequestInterface as ServerRequest;
use Slim\Psr7\Response;

class ValidarCuentaMW
{
    public function __invoke(Request $request, RequestHandler $handler): Response
    {   
        $parametros = $request->getParsedBody();
        $imagen = $request->getUploadedFiles();
        
        if(array_key_exists('nombre', $parametros) && array_key_exists('apellido', $parametros) &&
           array_key_exists('tipoDocumento', $parametros) && array_key_exists('nroDocumento', $parametros) &&
           array_key_exists('tipoDeCuenta', $parametros) && array_key_exists('email', $parametros) &&
           isset($imagen['imagen']) && $imagen['imagen']->getError() === UPLOAD_ERR_OK)
        {
            $response = $handler->handle($request);
        }else {
            $response = new Response();
            $payload = json_encode(array("mensaje" => "Error - Faltan datos"));
            $response->getBody()->write($payload);
        }

        return $response->withHeader('Content-Type', 'application/json');
    }
}