<?php
// Error Handling
error_reporting(-1);
ini_set('display_errors', 1);

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Factory\AppFactory;
use Slim\Routing\RouteCollectorProxy;
use Slim\Routing\RouteContext;

require __DIR__ . '/../vendor/autoload.php';

require_once './db/AccesoDatos.php';

require_once './controllers/CuentaController.php';
require_once './controllers/DepositoController.php';
require_once './controllers/RetiroController.php';
require_once './controllers/AjusteController.php';
require_once './controllers/UsuarioController.php';
require_once './controllers/LoginController.php';

require_once './middlewares/AuthMiddleware.php';
require_once './middlewares/ValidarCuentaMW.php';
require_once './middlewares/ValidarDepositoMW.php';
require_once './middlewares/ValidarRetiroMW.php';
require_once './middlewares/ValidarAjusteMW.php';

// Instantiate App
$app = AppFactory::create();

// Set base path
$app->setBasePath('/ProgramacionIII_SP/app');

// Add error middleware
$app->addErrorMiddleware(true, true, true);

// Add parse body
$app->addBodyParsingMiddleware();

// Routes

//JWT en login
$app->group('/auth', function (RouteCollectorProxy $group){
    $group->post('[/]', \LoginController::class . ':Login');
});
  
$app->group('/cuenta', function (RouteCollectorProxy $group) {
    $group->get('[/]', \CuentaController::class . ':TraerTodos');
    $group->get('/{nombre}', \CuentaController::class . ':TraerUno');
    $group->post('[/]', \CuentaController::class . ':CargarUno')->add(new ValidarCuentaMW());
    $group->put('/modificar', \CuentaController::class . ':Modificar');
    $group->delete('/baja', \CuentaController::class . ':Eliminar');
});

$app->group('/deposito', function (RouteCollectorProxy $group) {
    $group->get('[/]', \DepositoController::class . ':TraerTodos');
    $group->get('/{nombre}', \DepositoController::class . ':TraerUno');
    $group->post('[/]', \DepositoController::class . ':CargarUno')->add(new ValidarDepositoMW());
})->add(new AuthMiddleware('cajero'));

$app->group('/retiro', function (RouteCollectorProxy $group) {
    $group->get('[/]', \RetiroController::class . ':TraerTodos');
    $group->get('/{nombre}', \RetiroController::class . ':TraerUno');
    $group->post('[/]', \RetiroController::class . ':CargarUno')->add(new ValidarRetiroMW());
})->add(new AuthMiddleware('cajero'));

$app->group('/ajuste', function (RouteCollectorProxy $group) {
    $group->get('[/]', \AjusteController::class . ':TraerTodos');
    $group->get('/{nombre}', \AjusteController::class . ':TraerUno');
    $group->post('[/]', \AjusteController::class . ':CargarUno')->add(new ValidarAjusteMW());
})->add(new AuthMiddleware('supervisor'));

$app->group('/ConsultarCuenta', function (RouteCollectorProxy $group) {
    $group->post('[/]', \CuentaController::class . ':ConsultarCuenta');
});

$app->group('/ConsultaMovimientos/depositos', function (RouteCollectorProxy $group) {
    $group->get('/a', \DepositoController::class . ':ConsultarTotalDepositado');
    $group->get('/b', \DepositoController::class . ':ConsultarDepositosDeUsuario');
    $group->get('/c', \DepositoController::class . ':ConsultarEntreFechas');
    $group->get('/d', \DepositoController::class . ':ConsultarPorTipoDeCuenta');
})->add(new AuthMiddleware('operador'));

$app->group('/ConsultaMovimientos/retiros', function (RouteCollectorProxy $group) {
    $group->get('/a', \RetiroController::class . ':ConsultarTotalRetirado');
    $group->get('/b', \RetiroController::class . ':ConsultarRetirosDeUsuario');
    $group->get('/c', \RetiroController::class . ':ConsultarEntreFechas');
    $group->get('/d', \RetiroController::class . ':ConsultarPorTipoDeCuenta');
})->add(new AuthMiddleware('operador'));

$app->group('/usuario', function (RouteCollectorProxy $group) {
    $group->get('[/]', \UsuarioController::class . ':TraerTodos');
    $group->get('/{id}', \UsuarioController::class . ':TraerUno');
    $group->post('[/]', \UsuarioController::class . ':CargarUno');
});


$app->get('/ConsultaMovimientos/{nroDeCuenta}', \CuentaController::class . ':ConsultarOperaciones');


$app->run();