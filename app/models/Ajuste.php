<?php

class Ajuste
{
    public $id;
    public $nroDeOperacion;
    public $operacion;
    public $motivo;
    public $monto;

    public function crearAjuste()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("INSERT INTO ajustes (nroDeOperacion, operacion, motivo, monto) VALUES(:nroDeOperacion, :operacion, :motivo, :monto)");
        $consulta->bindValue(':nroDeOperacion', $this->nroDeOperacion, PDO::PARAM_INT);
        $consulta->bindValue(':operacion', $this->operacion, PDO::PARAM_STR);
        $consulta->bindValue(':motivo', $this->motivo, PDO::PARAM_STR);
        $consulta->bindValue(':monto', $this->monto, PDO::PARAM_STR);
        
        $consulta->execute();

        return $objAccesoDatos->obtenerUltimoId();
    }

    public static function obtenerTodos()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT id, nroDeOperacion, operacion, motivo, monto FROM ajustes");
        $consulta->execute();

        return $consulta->fetchAll(PDO::FETCH_CLASS, 'Ajuste');
    }

    public static function obtenerAjuste($id)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT id, nroDeOperacion, operacion, motivo, monto FROM ajustes WHERE id = :id");
        $consulta->bindValue(':id', $id, PDO::PARAM_INT);
        $consulta->execute();

        return $consulta->fetchObject('Ajuste');
    }

    public function modificarAjuste()
    {
        $objAccesoDato = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDato->prepararConsulta("UPDATE ajustes SET nroDeOperacion = :nroDeOperacion, operacion = :operacion, motivo = :motivo, monto = :monto WHERE id = :id");
        $consulta->bindValue(':id', (int)$this->id, PDO::PARAM_INT);
        $consulta->bindValue(':nroDeOperacion', $this->nroDeOperacion, PDO::PARAM_INT);
        $consulta->bindValue(':operacion', $this->operacion, PDO::PARAM_STR);
        $consulta->bindValue(':motivo', $this->motivo, PDO::PARAM_STR);
        $consulta->bindValue(':monto', $this->monto, PDO::PARAM_INT);
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

}