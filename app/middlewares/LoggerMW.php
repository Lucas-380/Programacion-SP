<?php
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Psr\Http\Message\ServerRequestInterface as ServerRequest;
use Slim\Psr7\Response;


class LoggerMW
{
    public function __invoke(Request $request, RequestHandler $handler): Response
    {   
        // Procesar la solicitud
        $response = $handler->handle($request);

        // Obtener la información necesaria para el registro
        $date = date('Y-m-d H:i:s');
        $user = $request->user; // Aquí deberías obtener el usuario de alguna manera
        $operacion = $request->operacion; // Debes obtener el número de operación de la solicitud

        // Crear el mensaje de registro
        $logMessage = "Fecha y Hora: $date | Usuario: $user | Número de Operación: $operacion\n";

        //guardar log en bd

        return $response;
    }
}