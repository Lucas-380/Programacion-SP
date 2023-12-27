<?php

class Usuario
{
    public $id;
    public $nombreDeUsuario;
    public $contrasenia;
    public $rol;

    public function crearUsuario()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        
        $consulta = $objAccesoDatos->prepararConsulta("INSERT INTO usuarios (nombreDeUsuario, contrasenia, rol) VALUES(:nombreDeUsuario, :contrasenia, :rol)");
        $consulta->bindValue(':nombreDeUsuario', $this->nombreDeUsuario, PDO::PARAM_STR);
        $consulta->bindValue(':contrasenia', $this->contrasenia, PDO::PARAM_STR);
        $consulta->bindValue(':rol', $this->rol, PDO::PARAM_STR);
        
        $consulta->execute();

        return $objAccesoDatos->obtenerUltimoId();
    }

    public static function obtenerTodos()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT nombreDeUsuario, contrasenia, rol FROM usuarios");
        $consulta->execute();

        return $consulta->fetchAll(PDO::FETCH_CLASS, 'Usuario');
    }

    public static function obtenerUser($id)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT nombreDeUsuario, contrasenia, rol FROM cuentas WHERE id = :id");
        $consulta->bindValue(':id', $id, PDO::PARAM_INT);
        $consulta->execute();

        return $consulta->fetchObject('Usuario');
    }

    public static function TraerUsuarioPorSesion($username, $contrasenia){
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT nombreDeUsuario, contrasenia, rol FROM usuarios WHERE nombreDeUsuario = :nombreDeUsuario AND contrasenia = :contrasenia" );
        $consulta->bindValue(':nombreDeUsuario', $username, PDO::PARAM_STR);
        $consulta->bindValue(':contrasenia', $contrasenia, PDO::PARAM_STR);
        $consulta->execute();

        return $consulta->fetchObject('Usuario');
    }

}