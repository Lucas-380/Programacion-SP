<?php

class Cuenta
{
    public $nroDeCuenta;
    public $nombre;
    public $apellido;
    public $tipoDocumento;
    public $nroDocumento;
    public $email;
    public $tipoDeCuenta; //  CA$ / CAU$S / CC$ / CCU$S
    public $saldo;
    public $imagen;
    public $fechaDeBaja;

    public function crearCuenta()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        
        $consulta = $objAccesoDatos->prepararConsulta("INSERT INTO cuentas (nombre, apellido, tipoDocumento, nroDocumento, email, tipoDeCuenta, saldo) VALUES(:nombre, :apellido, :tipoDocumento, :nroDocumento, :email, :tipoDeCuenta, :saldo)");
        $consulta->bindValue(':nombre', $this->nombre, PDO::PARAM_STR);
        $consulta->bindValue(':apellido', $this->apellido, PDO::PARAM_STR);
        $consulta->bindValue(':tipoDocumento', $this->tipoDocumento, PDO::PARAM_STR);
        $consulta->bindValue(':nroDocumento', $this->nroDocumento, PDO::PARAM_INT);
        $consulta->bindValue(':email', $this->email, PDO::PARAM_STR);
        $consulta->bindValue(':tipoDeCuenta', $this->tipoDeCuenta, PDO::PARAM_STR);
        $consulta->bindValue(':saldo', $this->saldo, PDO::PARAM_STR);
        
        $consulta->execute();

        return $objAccesoDatos->obtenerUltimoId();
    }

    public static function obtenerTodos()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT nroDeCuenta, nombre, apellido, tipoDocumento, nroDocumento, email, tipoDeCuenta, saldo, path_imagen AS imagen, fechaDeBaja FROM cuentas");
        $consulta->execute();

        return $consulta->fetchAll(PDO::FETCH_CLASS, 'Cuenta');
    }

    public static function obtenerCuenta($nroDeCuenta)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT nroDeCuenta, nombre, apellido, tipoDocumento, nroDocumento, email, tipoDeCuenta, saldo, path_imagen AS imagen, fechaDeBaja FROM cuentas WHERE nroDeCuenta = :nroDeCuenta");
        $consulta->bindValue(':nroDeCuenta', $nroDeCuenta, PDO::PARAM_STR);
        $consulta->execute();

        return $consulta->fetchObject('Cuenta');
    }

    public static function obtenerCuentaPorDocumento($nroDocumento)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT nroDeCuenta, nombre, apellido, tipoDocumento, nroDocumento, email, tipoDeCuenta, saldo, path_imagen AS imagen, fechaDeBaja FROM cuentas WHERE nroDocumento = :nroDocumento");
        $consulta->bindValue(':nroDocumento', $nroDocumento, PDO::PARAM_INT);
        $consulta->execute();

        return $consulta->fetchObject('Cuenta');
    }

    public function modificarCuenta()
    {
        $objAccesoDato = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDato->prepararConsulta("UPDATE cuentas SET nombre = :nombre, apellido = :apellido, tipoDocumento = :tipoDocumento, nroDocumento = :nroDocumento, email = :email, tipoDeCuenta = :tipoDeCuenta, saldo = :saldo, path_imagen = :path_imagen, fechaDeBaja = :fechaDeBaja WHERE nroDeCuenta = :nroDeCuenta");
        $consulta->bindValue(':nroDeCuenta', (int)$this->nroDeCuenta, PDO::PARAM_INT);
        $consulta->bindValue(':nombre', $this->nombre, PDO::PARAM_STR);
        $consulta->bindValue(':apellido', $this->apellido, PDO::PARAM_STR);
        $consulta->bindValue(':tipoDocumento', $this->tipoDocumento, PDO::PARAM_STR);
        $consulta->bindValue(':nroDocumento', $this->nroDocumento, PDO::PARAM_STR);
        $consulta->bindValue(':email', $this->email, PDO::PARAM_STR);
        $consulta->bindValue(':tipoDeCuenta', $this->tipoDeCuenta, PDO::PARAM_STR);
        $consulta->bindValue(':saldo', $this->saldo, PDO::PARAM_STR);
        $consulta->bindValue(':path_imagen', $this->imagen, PDO::PARAM_STR);
        $consulta->bindValue(':fechaDeBaja', $this->fechaDeBaja, PDO::PARAM_STR);
        $consulta->execute();
    }

    public static function borrarCuenta($nroDeCuenta)
    {
        $objAccesoDato = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDato->prepararConsulta("UPDATE cuentas SET fechaDeBaja = :fechaDeBaja WHERE nroDeCuenta = :nroDeCuenta");
        $fecha = (new DateTime('now', new DateTimeZone('America/Argentina/Buenos_Aires')))->format('Y/m/d');
        $consulta->bindValue(':nroDeCuenta', $nroDeCuenta, PDO::PARAM_INT);
        $consulta->bindValue(':fechaDeBaja', $fecha, PDO::PARAM_STR);
        return $consulta->execute();
    }

    public static function validarCuenta($cuentaAux) {
        $retorno = false;
        if($cuentaAux->nombre != '' && $cuentaAux->apellido != '' && $cuentaAux->nroDocumento != '' && $cuentaAux->email != '' &&
          ($cuentaAux->tipoDocumento == 'DNI' || $cuentaAux->tipoDocumento == 'CI' || $cuentaAux->tipoDocumento == 'PASAPORTE') && 
          ($cuentaAux->tipoDeCuenta == 'CA$' || $cuentaAux->tipoDeCuenta == 'CAU$S' || 
           $cuentaAux->tipoDeCuenta == 'CC$' || $cuentaAux->tipoDeCuenta == 'CCU$S'))
        {
            $cuenta = Cuenta::obtenerCuentaPorDocumento($cuentaAux->nroDocumento);
            if(!$cuenta)
            {
                $retorno = true;
            }
        }
        return $retorno;
    }
}