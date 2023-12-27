<?php

class Retiro
{
    public $id;
    public $fecha;
    public $monto;
    public $nroDeCuenta;

    public function crearRetiro()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("INSERT INTO retiros (fecha, monto, nroDeCuenta) VALUES(:fecha, :monto, :nroDeCuenta)");
        $consulta->bindValue(':fecha', $this->fecha, PDO::PARAM_STR);
        $consulta->bindValue(':monto', $this->monto, PDO::PARAM_STR);
        $consulta->bindValue(':nroDeCuenta', $this->nroDeCuenta, PDO::PARAM_INT);
        
        $consulta->execute();

        return $objAccesoDatos->obtenerUltimoId();
    }

    public static function obtenerTodos()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT id, fecha, monto, nroDeCuenta AS imagen FROM retiros");
        $consulta->execute();

        return $consulta->fetchAll(PDO::FETCH_CLASS, 'Retiro');
    }

    public static function obtenerRetiro($id)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT id, fecha, monto, nroDeCuenta FROM retiros WHERE id = :id");
        $consulta->bindValue(':id', $id, PDO::PARAM_STR);
        $consulta->execute();

        return $consulta->fetchObject('Retiro');
    }

    public function modificarRetiro()
    {
        $objAccesoDato = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDato->prepararConsulta("UPDATE depositos SET fecha = :fecha, nroDeCuenta = :nroDeCuenta WHERE id = :id");
        $consulta->bindValue(':id', (int)$this->id, PDO::PARAM_INT);
        $consulta->bindValue(':fecha', $this->fecha, PDO::PARAM_STR);
        $consulta->bindValue(':nroDeCuenta', $this->nroDeCuenta, PDO::PARAM_INT);
        $consulta->execute();
    }

    // public static function borrarCuenta($nroDeCuenta)
    // {
    //     $objAccesoDato = AccesoDatos::obtenerInstancia();
    //     $consulta = $objAccesoDato->prepararConsulta("UPDATE usuarios SET fechaDeBaja = :fechaDeBaja WHERE nroDeCuenta = :nroDeCuenta");
    //     $fecha = (new DateTime('now', new DateTimeZone('America/Argentina/Buenos_Aires')))->format('Y/m/d');
    //     $consulta->bindValue(':nroDeCuenta', $nroDeCuenta, PDO::PARAM_INT);
    //     $consulta->bindValue(':fechaDeBaja', $fecha, PDO::PARAM_STR);
    //     return $consulta->execute();
    // }

    public static function consultarRetirosEnFecha($fecha){
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT id, fecha, monto, nroDeCuenta FROM retiros WHERE DATE(fecha) = :fecha");
        $consulta->bindValue(':fecha', $fecha, PDO::PARAM_STR);
        $consulta->execute();

        return $consulta->fetchAll(PDO::FETCH_CLASS, 'Retiro');
    }

    public static function obtenerRetirosDeUsuario($nroDeCuenta)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT id, fecha, monto, nroDeCuenta FROM retiros WHERE nroDeCuenta = :nroDeCuenta");
        $consulta->bindValue(':nroDeCuenta', $nroDeCuenta, PDO::PARAM_INT);
        $consulta->execute();

        return $consulta->fetchAll(PDO::FETCH_CLASS, 'Retiro');
    }

    public static function obtenerRetirosEntreFecha($fechaInicio, $fechaFinal)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT id, fecha, monto, nroDeCuenta FROM retiros WHERE DATE(fecha) BETWEEN :fechaInicio AND :fechaFinal");

        $consulta->bindValue(':fechaInicio', $fechaInicio, PDO::PARAM_INT);
        $consulta->bindValue(':fechaFinal', $fechaFinal, PDO::PARAM_INT);
        $consulta->execute();

        return $consulta->fetchAll(PDO::FETCH_CLASS, 'Retiro');
    }
}