<?php

class Deposito
{
    public $id;
    public $fecha;
    public $monto;
    public $nroDeCuenta;
    public $imagen;

    public function crearDeposito()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("INSERT INTO depositos (fecha, monto, nroDeCuenta) VALUES(:fecha, :monto, :nroDeCuenta)");
        $consulta->bindValue(':fecha', $this->fecha, PDO::PARAM_STR);
        $consulta->bindValue(':monto', $this->monto, PDO::PARAM_STR);
        $consulta->bindValue(':nroDeCuenta', $this->nroDeCuenta, PDO::PARAM_INT);
        
        $consulta->execute();

        return $objAccesoDatos->obtenerUltimoId();
    }

    public static function obtenerTodos()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT id, fecha, monto, nroDeCuenta, path_imagen AS imagen FROM depositos");
        $consulta->execute();

        return $consulta->fetchAll(PDO::FETCH_CLASS, 'Deposito');
    }

    public static function obtenerDeposito($id)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT id, fecha, monto, nroDeCuenta, path_imagen AS imagen FROM depositos WHERE id = :id");
        $consulta->bindValue(':id', $id, PDO::PARAM_INT);
        $consulta->execute();

        return $consulta->fetchObject('Deposito');
    }

    public function modificarDeposito()
    {
        $objAccesoDato = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDato->prepararConsulta("UPDATE depositos SET fecha = :fecha, monto = :monto, nroDeCuenta = :nroDeCuenta, path_imagen = :path_imagen WHERE id = :id");
        $consulta->bindValue(':id', (int)$this->id, PDO::PARAM_INT);
        $consulta->bindValue(':fecha', $this->fecha, PDO::PARAM_STR);
        $consulta->bindValue(':monto', $this->monto, PDO::PARAM_STR);
        $consulta->bindValue(':nroDeCuenta', $this->nroDeCuenta, PDO::PARAM_INT);
        $consulta->bindValue(':path_imagen', $this->imagen, PDO::PARAM_STR);
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
    
    public static function consultarDepositosEnFecha($fecha){
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT id, fecha, monto, nroDeCuenta, path_imagen AS imagen FROM depositos WHERE DATE(fecha) = :fecha");
        $consulta->bindValue(':fecha', $fecha, PDO::PARAM_STR);
        $consulta->execute();

        return $consulta->fetchAll(PDO::FETCH_CLASS, 'Deposito');
    }

    public static function obtenerDepositosDeUsuario($nroDeCuenta)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT id, fecha, monto, nroDeCuenta, path_imagen AS imagen FROM depositos WHERE nroDeCuenta = :nroDeCuenta");
        $consulta->bindValue(':nroDeCuenta', $nroDeCuenta, PDO::PARAM_INT);
        $consulta->execute();

        return $consulta->fetchAll(PDO::FETCH_CLASS, 'Deposito');
    }

    public static function obtenerDepositosEntreFecha($fechaInicio, $fechaFinal)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT id, fecha, monto, nroDeCuenta, path_imagen AS imagen FROM depositos WHERE DATE(fecha) BETWEEN :fechaInicio AND :fechaFinal");

        $consulta->bindValue(':fechaInicio', $fechaInicio, PDO::PARAM_INT);
        $consulta->bindValue(':fechaFinal', $fechaFinal, PDO::PARAM_INT);
        $consulta->execute();

        return $consulta->fetchAll(PDO::FETCH_CLASS, 'Deposito');
    }

}