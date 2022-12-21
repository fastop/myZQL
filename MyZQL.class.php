<?php
/** ****************************************************
 *  @file MyZQL.class.php								 
 *														 
 *  @brief Clase para el manejo de datos mediante la
 *  manipulacion de PDO para CRUD.
 *													 
 * @author DRV                       			 
 * @date Enero 2020                           			 
 *            								  		 
 * @version 1.1+          			
 ****************************************************** */ 

class myZQL {

    private $CONN;

/**
 * CONSTRUCTOR
 */
    function __construct() {   //Inicializamos en el constructor...
                
        $datax=parse_ini_file("conf.ini");             
        $this->CONN = $this->conexion($datax["user"], $datax["passw"], $datax["db"], $datax["host"]);
    }
 
    /** 
     *   @brief Metodo para conectarnos por medio de PDO. 
     *
     *   @param usr     Usuario de la base de datos
     *   @param passw   Contraseña del usuario
     *   @param db      Nombre de la base de datos
     *   @param host    Direccion del servidor de la base de datos
     *
     *   @return 
     *     Si se realizó de manera correcta retorna el conector (Objeto PDO)
     *	  Si ocurrio algun error retorna el mensaje "Verifique los datos de su conexion" (String)
     */
    public function conexion($usr, $passw, $db, $host)
    {
        $connx="";

        try { $connx = new PDO("mysql:host=$host;dbname=$db;charset=utf8",$usr, $passw); //Nos conectamos...
        }catch(Exception $ex){  $connx = " Verifique los datos de su conexion " or die(); }
        
       return $connx;
    }

    /** 
     *   @brief Metodo para realizar una consulta general. 
     *   Anotación: utilizar este metodo puede llevar a inyeccciones sql si NO 
     *   se usa bien.
     *
     *   @param query:      Consulta personalizada (string)
     *   @param no_assoc:   Parámetro optativo. Al utilizarlo (true) Obtenemos los resultados NO asociativos SOLO numericos [0],[1], ... [n]. (bool)
     *   @param erro:       Parámetro optativo. Al utilizarlo como verdadero (true) obtenemos la descripción completa del error en caso de ocurrencia (bool)
     *
     *   @return resultado: (Array Asociativo | Array Vacio)
     */
    public function consultaME($query,$no_assoc=null ,$erro=null)
    {        
      $resultado=array();
      $opcx=PDO::FETCH_ASSOC;

      if($no_assoc)
        $opcx=PDO::FETCH_NUM;

        try{ $this->CONN->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            $sql = $this->CONN->prepare($query); // Obtener  todos los resultados de la base de datos
            $sql->execute();
            $resultado = $sql->fetchAll($opcx);
        }
        catch(PDOException $e) {
            if($erro)
                echo "Error: " . $e->getMessage();
        }

        return $resultado;
    }




/** 
 *   @brief Metodo para realizar consultas seguras
 *
 *   @param query     Consulta personalizada (SQL/String)
 *
 *   @return 
 *     Si se realizó de manera correcta retorna el resultado (Array Asociativo)
 *	   Si ocurrio algun error retorna el resultado vacio (Array Vacio)
 */
    public function consultaX($query)
    {
        // Obtener  todos los resultados de la base de datos
            $sql = $this->CONN->prepare($query);
            $sql->execute();
            $resultado = $sql->fetchAll();
        
        return $resultado;
    }

/**************************************************************** */
/****                   METODOS DE INSERCION                ***** */
/**************************************************************** */

    /** 
     *   @brief Metodo para realizar UNA insercion a base de datos.
     *
     *   @param query:    Consulta personalizada (SQL/String)
     *   @param erro:     Parámetro optativo. Al utilizarlo como verdadero (true) obtenemos la descripcion completa del error en caso de ocurrencia (Bool)
     *
     *   @return REX:     (bool)
     * 
     *    Si se realizó de manera correcta retorna Verdadero ( boolean :: 1)
     *	  Si ocurrio algun error retorna retorna Falso y el error completo en caso de haber colocado el segundo parametro. ( boolean :: 0 / boolean)
     */
    public function insertME($query, $erro=null)
    {
        $REX=false;
        $query = $this->cleanString($query); //Para "limpiar" lo que venga (se puede eliminar)

            try {
                    $this->CONN->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                    $this->CONN->exec($query);
                    $REX=true;
            }
            catch(PDOException $e){
                    if($erro)
                        echo "<strong> ERROR:</strong>  <br>". $e->getMessage()." <br><br><strong>CONSULTA:</strong> <br> ".$query . "<br>";
            }

        return $REX;
    }


    /** 
     *   @brief Metodo para realizar UNA insercion a base de datos regresando el ID del insetado
     *
     *   @param query:    Consulta personalizada (SQL/String)
     *   @param erro:     Parámetro optativo. Al utilizarlo como verdadero (true) obtenemos la descripcion completa del error en caso de ocurrencia (Bool)
     *
     *   @return lastInsertId:     (int)
     * 
     *    Si se realizó de manera correcta retorna el ID del elemento insertado
     *	  Si ocurrio algun error retorna retorna cero
     */    
    public function insertsME($query, $erro=null)
    {
        $lastInsertId=0;

        try {
                $this->CONN->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $this->CONN->exec($query);
                $lastInsertId=$this->CONN->lastinsertid();                
        }
        catch(PDOException $e){
            if($erro){
                    echo "<strong> ERROR:</strong>  <br>". $e->getMessage()." <br><br><strong>CONSULTA:</strong> <br> ".$query . "<br>";
                    $this->CONN->rollback();
            }
        }

      return $lastInsertId;
    }

/** 
 *   @brief Metodo para crear multiples inserciones de una sola vez.
 *   En caso de que exista UN SOLO ERROR en alguna insercion se deshace todo (ROLLBACK)
 *
 *   @param query:    Consulta personalizada separada por punto y coma(;) (SQL/String)
 *   @param erro:     Parámetro optativo. Al utilizarlo como verdadero (true) obtenemos la descripcion completa del error en caso de ocurrencia (Bool)
 *
 *   @return REX:       (bool)
 * 
 *    Si se realizó de manera correcta retorna Verdadero ( boolean :: 1)
 *	  Si ocurrio algun error retorna retorna Falso y el error completo en caso de haber colocado el segundo parametro. ( boolean :: 0 / boolean)
 */
    public function insertAll($query, $erro=null)
    { 
       $REX =false;

        try 
        {
            //Primero tomamos los queries
            $subQuery = explode(";",$query);

            // colocamos el PDO de error mode a exception
            $this->CONN->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);        

                $this->CONN->beginTransaction();

                for($i=0; $i<count($subQuery)-1; $i++)
                    $this->CONN->exec($subQuery[$i]);
    
                $this->CONN->commit();
                    
            $REX= true;

        }catch(PDOException $e) { $this->CONN->rollback();
            if($erro)
                echo "Error: " . $e->getMessage(); 
        }

 
        return $REX;
    }

 
/**************************************************************** */
/****                   METODOS DE MODIFICACION             ***** */
/**************************************************************** */


/** 
 *   @brief Metodo para modificar datos.
 *   Tenga cuidado con el uso de WHERE's
 *
 *   @param query:    Consulta personalizada (SQL/String)
 *   @param erro:     Parámetro optativo. Al utilizarlo como verdadero (true) obtenemos la descripcion completa del error en caso de ocurrencia (Bool)
 *
 *   @return REX:       (bool)
 *    Si se realizó de manera correcta (o incorrecta) retorna el numero de registros afectados ( number )
 *	  Si ocurrio algun error retorna retorna 0 y el error completo en caso de haber colocado el segundo parametro. ( boolean :: 0 / boolean)
 */
    public function modME($query, $erro=null)
    { 
       $REX= 0;

        try { $this->CONN->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $RES = $this->CONN->prepare($query);
                 $RES->execute();//ejecutamos la sentencia
                    
                $REX=$RES->rowCount();                
        }catch(PDOException $e) {  
              if($erro)
                echo "Error: " . $e->getMessage(); 
        }
 
        return $REX;
    }



/** 
 *   @brief Metodo para modificar datos de forma "segura".
 *
 *   @param table:   Tabla de la cual se modificaran los valores (String)
 *   @param datax:   Datos que se modificarán, si es mas de un dato separe con comas (nombre=xxxx, edad=xx, etc.)
 *   @param idx:     Parámetro de lo que se modificara (Where) (id=x, edad=x, etc.)
 *   @param erro:    Parámetro optativo. Al utilizarlo como verdadero (true) obtenemos la descripcion completa del error en caso de ocurrencia (Bool)
 *
 *   @return REX:    (Bool)
 *    Si se realizó de manera correcta (o incorrecta) retorna el numero de registros afectados ( number )
 *	  Si ocurrio algun error retorna retorna 0 y el error completo en caso de haber colocado el segundo parametro. ( boolean :: 0 / boolean)
 */
    public function modFrom($table, $datax, $idx, $erro=null)
    { 
       $REX= 0;

        $query="UPDATE ".$table." SET ".$datax." WHERE ".$idx;

        try { $this->CONN->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $RES = $this->CONN->prepare($query);
                 $RES->execute();//ejecutamos la sentencia
                    
                $REX=$RES->rowCount();                
        }catch(PDOException $e) {  
              if($erro)
                echo "Error: " . $e->getMessage(); 
        }
 
        return $REX;
    }


/** 
 *   @brief Metodo para llevar a cabo multiples modificaciones de una sola vez.
 *   En caso de que exista UN SOLO ERROR en alguna modificacion se deshacera todo (ROLLBACK)
 *
 *   @param query     Consulta personalizada separada por punto y coma(;) (SQL/String)
 *   @param erro     Parámetro optativo. Al utilizarlo como verdadero (true) obtenemos la descripcion completa del error en caso de ocurrencia (Bool)
 *
 *   @return 
 *    Si se realizó de manera correcta retorna Verdadero ( boolean :: 1)
 *	  Si ocurrio algun error retorna retorna Falso y el error completo en caso de haber colocado el segundo parametro. ( boolean :: 0 / String)
 */
    public function modAll($query, $erro=null)
    { 
       $REX =false;

        try 
        {
            //Primero tomamos los queries
            $subQuery = explode(";",$query);
 
            // colocamos el PDO de error mode a exception
            $this->CONN->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);        

                $this->CONN->beginTransaction();

                for($i=0; $i<count($subQuery)-1; $i++)
                    $this->CONN->exec($subQuery[$i]);
    
                $this->CONN->commit();
                    
            $REX= true;

        }catch(PDOException $e) {   $this->CONN->rollback(); 
            
            if($erro)
                echo "Error: " . $e->getMessage(); 
        }

 
        return $REX;
    }




/**************************************************************** */
/****                   METODOS DE ELIMINACION              ***** */
/**************************************************************** */

/** 
 *   @brief Metodo para eliminar datos.
 *   Tenga cuidado con el uso de WHERE's
 *
 *   @param query:    Consulta personalizada (SQL/String)
 *   @param erro:     Parámetro optativo. Al utilizarlo como verdadero (true) obtenemos la descripcion completa del error en caso de ocurrencia (Bool)
 *
 *   @return REX:     (bool)   
 *    Si se realizó de manera correcta (o incorrecta) retorna el numero de registros afectados ( number )
 *	  Si ocurrio algun error retorna retorna 0 y el error completo en caso de haber colocado el segundo parametro. ( boolean :: 0 / boolean)
 */
    public function delME($query, $erro=null)
    { 
       $REX= 0;

        try { $this->CONN->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $RES = $this->CONN->prepare($query);
                 $RES->execute();//ejecutamos la sentencia
                    
                $REX=$RES->rowCount();                
        }catch(PDOException $e) {  
              if($erro)
                echo "Error: " . $e->getMessage(); 
        }
 
        return $REX;
    }


/** 
 *   @brief Metodo para eliminar datos de manera "segura".
 *   para evitar problemas con los WHERE's solamente requerimos la tabla y el elemento que se eliminará
 *
 *   @param table   Tabla de la cual se eliminaran los valores (String)
 *   @param idx     Parámetro de lo que se eliminara (id=x, edad=x, etc.)
 *   @param erro   Parámetro optativo. Al utilizarlo como verdadero (true) obtenemos la descripcion completa del error en caso de ocurrencia (Bool)
 *
 *   @return 
 *    Si se realizó de manera correcta (o incorrecta) retorna el numero de registros afectados ( number )
 *	  Si ocurrio algun error retorna retorna 0 y el error completo en caso de haber colocado el segundo parametro. ( boolean :: 0 / boolean)
 */
    public function delFrom($table, $idx, $erro=null)
    { 
       $REX= 0;

       $query= "DELETE FROM ".$table." WHERE ".$idx;

        try { $this->CONN->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $RES = $this->CONN->prepare($query);
                 $RES->execute();//ejecutamos la sentencia
                    
                $REX=$RES->rowCount();                
        }catch(PDOException $e) {  
              if($erro)
                echo "Error: " . $e->getMessage(); 
        }
 
        return $REX;
    }    


/** 
 *   @brief Metodo para llevar a cabo multiples eliminaciones de una sola vez.
 *   En caso de que exista UN SOLO ERROR en alguna eliminacion se deshacera todo (ROLLBACK)
 *
 *   @param query:     Consulta personalizada separada por punto y coma(;) (SQL/String)
 *   @param erro:     Parámetro optativo. Al utilizarlo como verdadero (true) obtenemos la descripcion completa del error en caso de ocurrencia (Bool)
 *
 *   @return REX:       (bool)
 *    Si se realizó de manera correcta retorna Verdadero ( boolean :: 1)
 *	  Si ocurrio algun error retorna retorna Falso y el error completo en caso de haber colocado el segundo parametro. ( boolean :: 0 / String)
 */
    public function delAll($query, $erro=null)
    { 
       $REX =false;

        try 
        {
            //Primero tomamos los queries
            $subQuery = explode(";",$query);
         
            // colocamos el PDO de error mode a exception
            $this->CONN->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->CONN->exec("SET foreign_key_checks = 0");//Por el momento desactivamos la verificacion de LLAVES FORANEAS antes de hacer esto...

                $this->CONN->beginTransaction();

                for($i=0; $i<count($subQuery)-1; $i++)
                    $this->CONN->exec($subQuery[$i]);
    
                $this->CONN->commit();
                    
            $REX= true;
            $this->CONN->exec("SET foreign_key_checks = 1");

        }catch(PDOException $e) {   $this->CONN->rollback(); 
            
            if($erro)
                echo "Error: " . $e->getMessage(); 
        }

 
        return $REX;
    }




/**************************************************************** */
/****                   METODOS EXTRAS                      ***** */
/**************************************************************** */

/** 
 *   @brief Metodo para llevar obtener el numero de elementos consultados.
 *    Se utiliza DESPUES de llevar a cabo una consulta.
 *
 *   @param datax    Resultado de una consulta (Array)
 *
 *   @return 
 *    Si se realizó de manera correcta retorna el numero de elementos de la consulta ( number )
 *	  Si ocurrio algun error retorna retorna Falso y el error completo en caso de haber colocado el segundo parametro. ( boolean :: 0 / String)
 */
    public function howMany($datax){   
        return count($datax);
    }
 

/** 
 *   @brief Metodo para obtener el ID del ultimo elemento insertado
 *    Se utiliza DESPUES de llevar a cabo una insercion.
 *
 *   @return 
 *    Si se realizó de manera correcta retorna el ID del elemento insertado( number )
 */
    public function getLastId(){          
        return  $this->CONN->lastInsertId();    
    }
     

 /** 
 *   @brief Metodo para "limpiar" los datos que vengan con apostrofe.
 *    Se utiliza ANTES de lleva a cabo una insercion.
 *     Se recomienda que las sentencias no cuenten con punto y coma (;) al final
 *     ni comillas dobles dentro de la misma ("")
 * 
 *   @param TEXTO:    Resultado de una consulta (string)
 * 
 *   @return 
 *    Si se realizó de manera correcta retorna el STRING con apostrofes seguros ( string )
 */

    private function cleanString($TEXTO)
    {
        $TEXTO = trim($TEXTO); //Limpiamos todo
        $DDx = explode("VALUES",$TEXTO); //Partimos
    
        $s0 = substr(trim( $DDx[1]), 1, strlen(trim($DDx[1]))-2);//Quitamos los parentesis
        $SS = explode(",",$s0 );
    
        $dedalo = $tmp = "";
        
        foreach($SS as $dd)
        {                         
            if(stripos($dd,"'")!==false)//Buscamos caracter "extraño"
            {                        
                $s1 = substr(trim($dd), 1, strlen(trim($dd))-2); //Obtenemos la carnita ...
                $s2 = '"'.addslashes($s1).'"'; //Transformamos la shit de (') a \'                
                $tmp = $s2;
            }
            else
             $tmp = $dd;
    
           $dedalo.=$tmp.",";
        }
    
        $dedalo = rtrim($dedalo, ','); //QUitamos el último parentesis y unimos ...
    
    
        $FINAL = $DDx[0] ." VALUES (".$dedalo.")";
    
        return $FINAL;
    }



/**
 *  DESTRUCTOR
 */
   function __destruct() {
       $this->CONN=null; //Cerramo la conexion PDO
   }


/**************************************************************** */
/****                   METODOS DE SEGUROS                ***** */
/**************************************************************** */

/** 
 *   @brief Metodo para realizar una consulta general. 
 *   Anotación: utilizar este metodo puede llevar a inyeccciones sql si NO 
 *   se usa bien.
 *
 *   @param query      Consulta personalizada (SQL)
 *   @param data      Datos para preparar el statement (SQL) 
 *   @param no_assoc   Parámetro optativo. Al utilizarlo (true) obtenemos los resultados NO asociativos SOLO numericos [0],[1], ... [n].
 *   @param erro       Parámetro optativo. Al utilizarlo como verdadero (true) obtenemos la descripción completa del error en caso de ocurrencia (Bool)
 *
 *   @return 
 *    Si se realizó de manera correcta retorna el resultado (Array Asociativo)
 *	  Si ocurrio algun error retorna el resultado vacio (Array Vacio)
 *    En caso de utlizar el segundo parámetro se regresará una descripción completa del error.
 */
public function consultaME_SEC($query, $data ,$no_assoc=null ,$erro=null)
{ 
    $resultado=array();
    $opcx=PDO::FETCH_ASSOC;
  
    if($no_assoc)
      $opcx=PDO::FETCH_NUM;

      try{ $this->CONN->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $sql = $this->CONN->prepare($query); // Obtener  todos los resultados de la base de datos
            $keys = array_keys($data);//Obtenemos los nombres

            foreach($keys as $i => $key )
                $sql->bindParam(':'.$key, $data[$key]);

            $sql->execute();
            $resultado = $sql->fetchAll($opcx);
        }
        catch(PDOException $e) {
            if($erro)
                echo "Error: " . $e->getMessage();
        }
        
  return $resultado;

}










}//FINdeCLASE

?>