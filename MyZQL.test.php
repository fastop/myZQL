<?php
/** ****************************************************
 *  @file MyZQL.test.php								 
 *														 
 *  @brief Archivo para probar la clase MyZQL
 *													 
 * @author DRV                       			 
 * @date Enero 2020 updated Diciembre 2022
 *            								  		 
 * @version 1.3+          			
 ****************************************************** */ 

    require_once("MyZQL.class.php");

    $CON= new myZQL(); //Creamos la instancia de la clase...
    
echo "<pre>";//Para formatear la salida de print_r
    
/** *************************************
 *            CONSULTAS NORMALES
 **************************************** */

    //Con TRUE regresamos un arreglo NO asociativo
    # $resultado = $CON->consultaME("SELECT * FROM labors", true); //Deje el asterisco por son EJEMPLOS!
    # print_r($resultado);

    // Con arreglo asociativo
    # $resultado = $CON->consultaME("SELECT * FROM labors");   
    # print_r($resultado);
    

/** *************************************
 *       CONSULTAS CON "SEGURIDAD"
 **************************************** */

        //Consulta segura SENCILLA
 
        # $data["labor"] = "Packing"; 
        # $sql = "SELECT id_labor, labor, status FROM labors WHERE labor = :labor";
        # 
        # $REX = $CON->consultaME_SEC($sql, $data);
        # print_r($REX);
        

        //Consulta segura con MAS elementos        
        
        # $data["labor"] = "Packing"; 
        # $data["status"] = "1"; 
        # $sql = "SELECT id_labor, labor, status FROM labors WHERE labor = :labor AND status <> :status";
        # 
        # $REX = $CON->consultaME_SEC($sql, $data);
        # print_r($REX);
        
 

/************************************************************* */
/*    INSERTANDO ELEMENTOS
/************************************************************* */

    //Insertando UN solo elemento (sencillo)
     # $query = "INSERT INTO log (id_log,title,description,date,id_log_type,user,status) VALUES (0,'Titulin', 'Descripcion','1000-01-01 00:00:00',1,1,0)";
     #   echo $CON->insertME($query); //1 si inserto, 0 si no ...



   //Insertando VARIOS elementos de un jalon
    # $query="INSERT INTO log (id_log,title,description,date,id_log_type,user,status) VALUES (0,'Titulin', 'Descripcion','1000-01-01 00:00:00',1,1,0);";
    # $query.="INSERT INTO log (id_log,title,description,date,id_log_type,user,status) VALUES (0,'Titulin', 'Descripcion','1000-01-01 00:00:00',1,1,0);";
    # $query.="INSERT INTO log (id_log,title,description,date,id_log_type,user,status) VALUES (0,'Titulin', 'Descripcion','1000-01-01 00:00:00',1,1,0);";
    # $query.="INSERT INTO log (id_log,title,description,date,id_log_type,user,status) VALUES (0,'Titulin', 'Descripcion','1000-01-01 00:00:00',1,1,0);";
    # //Es SUMAMENTE IMPORTANTE colocar punto y coma (;) al final de cada sentencia ...
    # 
    #  echo $CON->insertAll($query,true);


   //Insertando UN elemento y OBTENIENDO el ID del elemento insertado
   # $query = "INSERT INTO log (id_log,title,description,date,id_log_type,user,status) VALUES (0,'Titulin', 'Descripcion','1000-01-01 00:00:00',1,1,0)";
   #  echo $CON->insertsME($query); //1 si inserto, 0 si no ...
 



/************************************************************* */
/*    ACTUALIZANDO ELEMENTOS
/************************************************************* */

    //Actualizacion Sencilla
     # $quero="UPDATE log SET description = 'CAMBIO X' WHERE id_log = 4";
     #   echo $CON->modMe($quero,true);


    //Actualizacion Sencilla PERSONALIZADA seleccionando tabla, campos y condicional
     #   echo $CON->modFrom("log","description='CAMBIO X', user=2","id_log= 4");
 

    //Insertando VARIOS elementos de un jalon
     # $querom= "UPDATE log SET description = 'CAMBIO X1' WHERE id_log = 1;";
     # $querom.="UPDATE log SET description = 'CAMBIO X2' WHERE id_log = 3;";
     # $querom.="UPDATE log SET description = 'CAMBIO X3' WHERE id_log = 4;";
     # $querom.="UPDATE log SET description = 'CAMBIO X4' WHERE id_log = 5;";
     # //Es SUMAMENTE IMPORTANTE colocar punto y coma (;) al final de cada sentencia ...
     #
     # echo $CON->modAll($querom);

 

/************************************************************* */
/*    ELIMINANDO ELEMENTOS
/************************************************************* */

    //Eliminando UN elemento (sencillo)
    # $querod = "DELETE FROM log WHERE id_log=1";
    #  echo $CON->delME($querod);


    //Eliminando al vuelo de forma PERSONALIZADA
    # echo $CON->delFrom("log","id_log=3");


    //Eliminando MUCHOS ELEMENTOS a la vez 

     #   $querodx="DELETE FROM log WHERE id_log = 2;";
     #   $querodx.="DELETE FROM log WHERE id_log = 4;";
     # //Es SUMAMENTE IMPORTANTE colocar punto y coma (;) al final de cada sentencia ...        

     #   echo $CON->delAll($querodx);  




/************************************************************* */
/*    FUNCIONES EXTRAS
/************************************************************* */

    //Obtenemos la cantidad de elementos en dicha tabla
    #    $resultado = $CON->consultaME("SELECT * FROM labors");
    #    echo $CON->howMany($resultado); //Se podria usar un COUNT pero es mas comodo asi :P

 
 

?>