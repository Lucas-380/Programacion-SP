<?php
interface IApiUsable
{
	public function TraerUno($request, $response, $args);
	public function TraerTodos($request, $response, $args);
	public function CargarUno($request, $response, $args);
	public function Modificar($request, $response, $args);
	public function Eliminar($request, $response, $args);
}