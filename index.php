<?php
	mb_internal_encoding("UTF-8");
	session_start();
	
	require 'vendor/autoload.php';
	
	$twigView = new \Slim\Extras\Views\Twig();	
	
	$app = new \Slim\Slim(array(
	    'view' => $twigView,
	    'templates.path' => __DIR__.'/templates/'
	));


	/*
		Genera la pagina principal
	 */
  	$app->get('/', function() use ($app) {
		
  		if( !isset( $_SESSION['usuario'] ) ){
			$app->render('index.twig.html', array(	            
		       'path' => "./",
		    ));
	    }
	    else{
	    	// ya se encuentra logueado.
	    	$usuario = $_SESSION['usuario'];
	    	
	    	$app->render('index.twig.html', array(
	    		'path' => './',
	    		'usuario' => $usuario,
    		));	    	

	    }
    });


  	/*
  		Se encarga de finalizar la sesion
  	 */
  	$app->get('/logout', function() use ($app){
  		$_SESSION['usuario'] = null;
  		$app->redirect('./');
  	});

  	/*
  		Se encarga del login
  	 */
  	$app->post('/login', function() use ($app){

  		$req=  $app->request();
  		$user = $req->post('user');
  		$password = $req->post('password');

  		wlog("Login :: User:".$user." Password:".$password);

  		$usuario = comprobarLogin($user,$password);



  		// Si se logueo correctamente lo dirigimos al index
  		if($usuario){
  			$_SESSION['usuario'] = $usuario;
  			$_SESSION['carrito'] = obtenerCarrito($usuario);
  			$app->redirect('./');

  		//Le avisamos que no se logueo correctamente
  		} else {
  			$app->render('index.twig.html', array(	            
		       'path' => "./",
		       'mensaje' => "Usuario o contraseña invalido",
		    ));
  		}

  	});

  	/*
  		Devuelve la lista de todos los productos
  		que existen en la tienda
  	 */
  	$app->get('/lista', function() use ($app) {  	
  		
  		$usuario = null;
  		$carrito = null;
  		if( isset($_SESSION['usuario']) ){
  			$usuario = $_SESSION['usuario'];
  			$carrito = obtenerCarrito($usuario);
  		}

	    $categorias = getCategorias();
	    $articulos = getArticulos();

  		if($articulos && $categorias){
	       $fin = 0;
  			if ( count($articulos) < 5 ) {
  				$fin = 1;
  			}



	        $app->render('lista.twig.html', array(	            
	            'articulos' => $articulos,
	            'categorias' => $categorias,
	            'page' => 0,
	            'path' => './',
	            'fin' => $fin,
	            'usuario' => $usuario,
	            'carrito' => $carrito,
	        ));



	    }
	    else {
	    	;
	    }	    
    });


	/*
		Devuelve los productos de acuerdo a los 
		parametros del filtro de busqueda
	 */
	$app->post('/lista/buscar', function() use ($app) {  		
  		$req=  $app->request();
  		$query = $req->post('query');
  		$desde = $req->post('desde');
  		$hasta = $req->post('hasta');
  		$idCategoria = $req->post('idCategoria');  		
  		

	    $categorias = getCategorias();
	    $articulos = filtrarArticulos($query,$idCategoria,$desde,$hasta);

	    $usuario = null;
  		$carrito = null;
  		if( isset($_SESSION['usuario']) ){
  			$usuario = $_SESSION['usuario'];
  			$carrito = obtenerCarrito($usuario);
  		}

       $fin = 0;
		if ( count($articulos) < 5 ) {
			$fin = 1;
		}

		$mensaje = null;
		if(!$articulos){
			$mensaje = "No se encontraron resultados";
		} 

        $app->render('lista.twig.html', array(	            
            'articulos' => $articulos,
            'categorias' => $categorias,
            'usuario' => $usuario,
            'carrito' => $carrito,
            'page' => 0,
            'path' => '../',
            'fin' => $fin,
            'mensaje' => $mensaje,
        ));

	    
    });

	
	/*
		Genera la lista de productos de una categoria en especifico
	 */
	$app->get('/lista/categoria/:id', function($id) use ($app) {  		
  		$idCategoria = $id;

  		$articulos = filtrarArticulos("",$id,null,null);
	    $categorias = getCategorias();

	    $usuario = null;
  		$carrito = null;
  		if( isset($_SESSION['usuario']) ){
  			$usuario = $_SESSION['usuario'];
  			$carrito = obtenerCarrito($usuario);
  		}

  		if($articulos && $categorias){
	        $fin = 0;
  			if ( count($articulos) < 5 ) {
  				$fin = 1;
  			}
	        $app->render('lista.twig.html', array(	            
	            'articulos' => $articulos,
	            'categorias' => $categorias,
	            'page' => 0,
	            'path' => '../../',
	            'fin' => $fin,
	            'usuario' => $usuario,
	            'carrito' => $carrito,
	        ));
	    }
	    else {
	    	$app->render('lista.twig.html', array(	            
	            'articulos' => $articulos,
	            'categorias' => $categorias,
	            'page' => 0,
	            'path' => '../../',
	            'usuario' => $usuario,
	            'carrito' => $carrito,
	            'mensaje' => "No hay articulos en esta categoria",
	        ));
	    }	   
    });

	
	/*
		Genera la pagina Acerca de...
	 */
	$app->get('/acerca', function() use ($app) {
  		

	    $categorias = getCategorias();

  		if($categorias){

	       $fin = 0;

	        $app->render('acerca.twig.html', array(
	            'foo' => 'version 0.1',
	            'categorias' => $categorias,
	            'page' => 0,
	            'path' => './',
	            'fin' => $fin,
	        ));
	    }
	    else {
	    	;
	    }
	    
    });


	/*
		Genera una pagina con el contenido del carrito del
		usuario (la lista de articulos en su carrito).
	 */
	$app->get('/carrito/:id', function($id) use ($app) {
  		
		$usuario = null;
  		$carrito = null;
  		$articulos = null;
  		if( isset($_SESSION['usuario']) ){
  			$usuario = $_SESSION['usuario'];
  			$carrito = obtenerCarrito($usuario);
  			$articulos = getDetalleCarrito($carrito->IdCarrito);
  		}
	    

	    $mensaje = null;
  		if(!$articulos){
  			$mensaje = "Su carrito esta vacio";
  		}

	    $app->render('carrito.twig.html', array(	            
	            'articulos' => $articulos,
	            'usuario' => $usuario,
	            'carrito' => $carrito,
	            'page' => 0,
	            'path' => '../',
        		'mensaje' => $mensaje,
        ));

    });





	/*
		Genera la pagina para agregar un producto a 
		el carrito
	 */
	$app->get('/carrito/agregar/:id', function($id) use ($app) {

		$usuario = null;
  		$carrito = null;
  		$articulo = null;
  		if( isset($_SESSION['usuario']) ){
  			$usuario = $_SESSION['usuario'];
  			$carrito = obtenerCarrito($usuario);
  			$articulo = getArticulo($id);
  		}

  		//Solo si tenemos un carrito con el usuario y el id es valido
  		if($articulo && $usuario && $carrito){


  			$hayItem = carritoHasItem($carrito,$articulo);


		  	//Si existe el item en el carrito
		  	if($hayItem){
		  		
		  		$detalle = getDetalleByCarritoItem($carrito,$articulo);

		  		$app->render('articulo.twig.html', array(
		            'usuario' => $usuario,
		            'carrito' => $carrito,
		            'articulo' => $articulo,
		            'path' => '../../',
		            'detalle' => $detalle
		        ));
		  	}
		  	// Si no existe en el carrito
		  	elseif(!$hayItem){		
	  			$app->render('articulo.twig.html', array(
		            'usuario' => $usuario,
		            'carrito' => $carrito,
		            'articulo' => $articulo,
		            'path' => '../../'
		        ));
	  		}
	  		//Ocurrio un error
	  		elseif ($hayItem == -1) {
	  			$categorias = getCategorias();
	  			$articulos = getArticulos();
	  			$app->render('lista.twig.html', array(	            
		            'articulos' => $articulos,
		            'categorias' => $categorias,     
		            'path' => '../../',
		            'usuario' => $usuario,
		            'carrito' => $carrito,
		            'mensaje' => "Ocurrio un error al ordenar el item",
		        ));
	  		}

  		//Ocurrio algun tipo de error
  		}else{
  			 $categorias = getCategorias();
  			 $articulos = getArticulos();
  			 $app->render('lista.twig.html', array(	            
	            'articulos' => $articulos,
	            'categorias' => $categorias,     
	            'path' => '../../',
	            'usuario' => $usuario,
	            'carrito' => $carrito,
	            'mensaje' => "Ocurrio un error al ordenar el item",
	        ));
  		}

	});



	/*
		Recibe los datos del item que se agrega al carrito
	 */
	
	$app->post('/carrito/add', function() use($app){

		$req=  $app->request();
  		$IdArticulo = $req->post('IdArticulo');
  		$cantidad = $req->post('cantidad');

	    $usuario = null;
  		$carrito = null;
  		if( isset($_SESSION['usuario']) ){
  			$usuario = $_SESSION['usuario'];
  			$carrito = obtenerCarrito($usuario);

  			$articulo = getArticulo($IdArticulo);

  			$seAnadio = addItemCarrito($carrito->IdCarrito, $articulo->IdArticulo, $cantidad );

  			if($seAnadio){
  				$mensaje = "Se ha agregado el articulo con exito";
  				//Actualizamos el carrito.
  				$carrito = obtenerCarrito($usuario);
  			}else{
  				$mensaje = "ERROR!! No se pudo agregar el articulo";
  			}

  		}else{
  			$mensaje = "ERROR!! No se pudo agregar el articulo";
  		}

		$articulos = getArticulos();
		$categorias = getCategorias();

        $app->render('lista.twig.html', array(	            
            'articulos' => $articulos,
            'categorias' => $categorias,
            'usuario' => $usuario,
            'carrito' => $carrito,
            'page' => 0,
            'path' => '../',
            'mensaje' => $mensaje,
        ));


	});


	/*
		Borra un item del carrito
	 */
	$app->post('/carrito/delete', function() use($app){

		$req=  $app->request();
  		$IdArticulo = $req->post('IdArticulo');
		$seBorro = false;
		$usuario = null;
  		$carrito = null;
  		$articulos = null;
  		$mensaje = null;
  		if( isset($_SESSION['usuario']) ){
  			$usuario = $_SESSION['usuario'];
  			$carrito = obtenerCarrito($usuario);
  			$seBorro = deleteItemCarrito($carrito->IdCarrito, $IdArticulo);
  			$articulos = getDetalleCarrito($carrito->IdCarrito);
  		}
	    
	    
	    if($seBorro){
	    	$mensaje = "Se elimino el articulo del carrito";
	    }else{
	    	$mensaje = "Ocurrio un error, no se pudo eliminar el articulo del carrito.";
	    }


  		if(!$articulos){
  			$mensaje = $mensaje."\nSu carrito esta vacio";
  		}

	    $app->render('carrito.twig.html', array(	            
	            'articulos' => $articulos,
	            'usuario' => $usuario,
	            'carrito' => $carrito,
	            'page' => 0,
	            'path' => '../',
        		'mensaje' => $mensaje,
        ));
	});


	/*
		Registra un nuevo usuario
	 */
  	$app->post('/registrar', function() use ($app){

  		$req=  $app->request();
  		$user = $req->post('user');
  		$password = $req->post('password');
  		$nombre = $req->post('nombre');
  		$apellido = $req->post('apellido');

  		wlog("Registrar :: User:".$user." Password:".$password);


  		$seCreo = nuevoCliente($user,$password,$nombre,$apellido);

  		if($seCreo){
	  		$usuario = comprobarLogin($user,$password);



	  		// Si se logueo correctamente lo dirigimos al index
	  		if($usuario){
	  			$_SESSION['usuario'] = $usuario;
	  			$_SESSION['carrito'] = obtenerCarrito($usuario);
	  			$app->redirect('./');

	  		//Le avisamos que no se logueo correctamente
	  		} else {
	  			$app->render('index.twig.html', array(	            
			       'path' => "./",
			       'mensaje' => "Usuario o contraseña invalido",
			    ));
	  		}
  		}else{
  			$app->render('index.twig.html', array(	            
			       'path' => "./",
			       'mensaje' => "No se pudo crear el usuario",
			    ));
  		}

  	});


	/*
		Realiza el paso final de la compra.
	 */
	
	  	/*
  		Devuelve la lista de todos los productos
  		que existen en la tienda
  	 */
  	$app->get('/checkout', function() use ($app) {  	
  		
  		$usuario = null;
  		$carrito = null;
  		$seCompro = false;
  		if( isset($_SESSION['usuario']) ){
  			$usuario = $_SESSION['usuario'];
  			$carrito = obtenerCarrito($usuario);
  			$seCompro = confirmarCompra($carrito);
  		}

	    $categorias = getCategorias();
	    $articulos = getArticulos();

  		if($articulos && $categorias && $seCompro){

  			enviarMail($carrito);

  			$mensaje = "Se ha realizado su compra con exito. Revise su correo electronico para mas informacion";
  			$carrito = obtenerCarrito($usuario);
	        $app->render('lista.twig.html', array(	            
	            'articulos' => $articulos,
	            'categorias' => $categorias,
	            'path' => './',
	            'usuario' => $usuario,
	            'carrito' => $carrito,
	            'mensaje' => $mensaje
	        ));



	    }
	    else {

	    	$mensaje = "Ocurrio un problema al procesar su compra.";

	    	$articulos = getDetalleCarrito($carrito->IdCarrito);

	    	$app->render('carrito.twig.html', array(	            
	            'articulos' => $articulos,
	            'usuario' => $usuario,
	            'carrito' => $carrito,
	            'page' => 0,
	            'path' => './',
        		'mensaje' => $mensaje,
        	));
	    }	    
    });
	
	$app->run();





	function nuevoCliente($user,$pass,$nombre,$apellido){

		$sql = "INSERT INTO `cliente` (`IdCliente`, `Cedula`, `Nombres`, `Apellidos`, `Telefono`, ".
				"`Direccion`, `IdDepartamento`, `IdCiudad`) VALUES (NULL, NULL, '"
				.$nombre."', '".$apellido."', NULL, NULL, NULL, NULL);";

		try{
	    		wlog("nuevoCliente :: Se intenta crear el cliente");
	    		$db = getConnection(); 
		        $stmt = $db->query($sql);
		        $idCliente = $db->lastInsertId();
		        $db = null;

		        $sql = "INSERT INTO `gadgetstore`.`user` (`idUser`, `idClienteFK`, `user`, `pass`, `ultima_fecha`)".
		        		" VALUES (NULL, '".$idCliente."', '".$user."', '".$pass."', NULL);";

		        wlog("nuevoCliente :: Se intenta crear el user");
	    		$db = getConnection(); 
		        $stmt = $db->query($sql);
		        $idUser = $db->lastInsertId();
		        $db = null;

		        if($idUser){
		        	return true;
		        }

	    }catch(PDOException $e){
	    		wlog ( "addItem :: ERROR :: ".$e->getMessage() );
	    		return false;
	    }
	    return false;

	}

	/*
		Borra un item del carrito
	 */

	function deleteItemCarrito($idCarrito,$idItem){

		$sql = "DELETE FROM `detallecarrito` WHERE `detallecarrito`.`IdCarrito` = ".$idCarrito. 
				" AND `detallecarrito`.`IdArticulo` = ".$idItem." ;";
		
		try{
	    		wlog("deleteITem :: Se intenta borrar item al carrito");
	    		$db = getConnection(); 
		        $stmt = $db->query($sql);
		        $db = null;

		        //Se agrego el detalle
		        return actualizarCarrito($idCarrito);

	    }catch(PDOException $e){
	    		wlog ( "addItem :: ERROR :: ".$e->getMessage() );
	    		return false;
	    }
	    return false;
	}

	/*
		Comprueba si un item ya se encuentra dentro 
		del carrito
	 */
	
	function carritoHasItem($carrito, $item){
		$idItem = $item->IdArticulo;
		$idCarrito = $carrito->IdCarrito;

		$sql = "SELECT * FROM detallecarrito WHERE IdCarrito = ".$idCarrito." AND IdArticulo = ".$idItem." ;";

		try{

			wlog("carritoHasItem :: Intenta ejecutar ". $sql);
			$db = getConnection();
			$stmt = $db->query($sql);
			$detalle = $stmt->fetch(PDO::FETCH_OBJ);
	        $db = null;	 
	        
	        //Si es que encontro quiere decir que existe el item dentro
	        //del carrito
	        if($detalle){
	        	return true;
	        }else{
	        	return false;
	        }

		}catch(PDOException $e){
			wlog("carritoHasItem :: Error :: ".$e->getMessage());
			return -1;
		}

		return -1;

	}

	/*
		Retorna un detalle que esta dentro de un carrito
	 */
	function getDetalleByCarritoItem($carrito, $item){
		$idItem = $item->IdArticulo;
		$idCarrito = $carrito->IdCarrito;

		$sql = "SELECT * FROM detallecarrito WHERE IdCarrito = ".$idCarrito." AND IdArticulo = ".$idItem." ;";

		try{

			wlog("carritoHasItem :: Intenta ejecutar ". $sql);
			$db = getConnection();
			$stmt = $db->query($sql);
			$detalle = $stmt->fetch(PDO::FETCH_OBJ);
	        $db = null;	 
	        
	        //Si es que encontro quiere decir que existe el item dentro
	        //del carrito
	        if($detalle){
	        	return $detalle;
	        }

		}catch(PDOException $e){
			wlog("carritoHasItem :: Error :: ".$e->getMessage());
			return null;
		}

		return null;

	}

	/*
		Añadimos un item al carrito
	 */
	function addItemCarrito($idCarrito, $idProducto, $cantidad){


		$articulo = getArticulo($idProducto);
		$carrito = getCarritoById($idCarrito);

		$subTotal = $articulo->Precio * $cantidad;

		$hayItem = carritoHasItem($carrito, $articulo);

		//Si ya contiene el item, entonces es una actualizacion
		if($hayItem){
			$sql = 	"UPDATE `gadgetstore`.`detallecarrito` "
					."SET `Cantidad` = '". $cantidad ."', `SubTotal` = '". $subTotal ."' ".
					"WHERE `detallecarrito`.`IdCarrito` = ".$idCarrito.
					" AND `detallecarrito`.`IdArticulo` = ".$idProducto.";";

		//Si no, debemos de crear un nuevo detalle
		}else{		
			$sql = "INSERT INTO `detallecarrito` (`IdCarrito`, `IdArticulo`, `Cantidad`, `SubTotal`)".
				" VALUES ('".$idCarrito."', '".$idProducto."', '".$cantidad."', '".$subTotal."');";
		}


		try{
	    		wlog("addITem :: Se intenta agregar item al carrito");
	    		$db = getConnection(); 
		        $stmt = $db->query($sql);
		        $db = null;

		        //Se agrego el detalle
		        return actualizarCarrito($idCarrito);

	    }catch(PDOException $e){
	    		wlog ( "addItem :: ERROR :: ".$e->getMessage() );
	    		return false;
	    }
	    return false;

	}

	/*
		Actualiza los datos de un carrito
		Recalculando de acuerdo a los detalles.
	 */
	function actualizarCarrito($idCarrito){
		
		$articulos = getDetalleCarrito($idCarrito);
		$total = 0;
		$cantArt = 0;
		//Si hay articulos
		if( $articulos ){
			
			$cantArt = count($articulos);
			for ($i=0; $i<count($articulos); $i++){
				$total = $total + $articulos[$i]->SubTotal;
			}

			$sql = "UPDATE `carrito` SET `CantArt` = ".$cantArt.", `MontoTotal` = ".$total.
					" WHERE `carrito`.`IdCarrito` =".$idCarrito.";";
		

	    	try{
	    		wlog("actualizarCarrito :: Se intenta actualizar carrito");
	    		$db = getConnection(); 
		        $stmt = $db->query($sql);
		        $db = null;

		        //Suponemos que para este punto el carrito ya fue actualizad.
		        return true;

	    	}catch(PDOException $e){
	    		wlog ( "actualizarCarrito :: ERROR :: ".$e->getMessage() );
	    		return false;
	    	}
		}

		return false;

	}

	/*
		Confirma una compra
	 */
	function confirmarCompra($carrito){

		$idCarrito = $carrito->IdCarrito;

		$sql = "UPDATE `carrito` SET `Activo` = '0' WHERE `carrito`.`IdCarrito` = ".$idCarrito.";";

		try{
	    		wlog("actualizarCarrito :: Se intenta actualizar carrito");
	    		$db = getConnection(); 
		        $stmt = $db->query($sql);
		        $db = null;

		        //Suponemos que para este punto el carrito ya fue actualizad.
		        return true;

	    	}catch(PDOException $e){
	    		wlog ( "actualizarCarrito :: ERROR :: ".$e->getMessage() );
	    		return false;
	    	}
		return false;

	}

	/*
		Comprueba si existe un usuario con un password
		Si existe retorna todos sus datos.
		Si no, retorna false.
	 */
  	function comprobarLogin($user, $pass){
		$sql = "select * from user join cliente on user.idClienteFK = cliente.IdCliente where user.user = :user AND user.pass = :pass";
	    try {
	        $db = getConnection();
            $stmt = $db->prepare($sql); 
	        $stmt->bindParam("user", $user);
	        $stmt->bindParam("pass", $pass);
	        $stmt->execute();
	        $usuario = $stmt->fetch(PDO::FETCH_OBJ);
	        $db = null;	 
	        
	        if($usuario){
	        	return $usuario;
	        }


	    } catch(PDOException $e) {
	        wlog("comprobarLogin :: ERROR :: ". $e->getMessage() );
	        return false;
	    }
	    return false;
  	}

  	/*
  		Obtener ultimo Carrito del Usuario.
  		Caso contrario, si no hay uno activo,
  		crear un nuevo carrito.
  	 */
  	function obtenerCarrito($usuario){

  		$idCliente = $usuario->IdCliente;

  		
	    $carrito = getCarrito($idCliente);
        //Existe un carrito activo, entonces retornamos el mismo
        if($carrito){

        	wlog("obtenerCarrito :: Tiene Carrito Activo. Carrito id : " . $carrito->IdCarrito);
        	return $carrito;
        
        //No existe un carrito activo, debemos crear uno
        }else{	
        	$carrito = nuevoCarrito($idCliente);
        	wlog("obtenerCarrito :: Nuevo y ahora tiene Carrito Activo. Carrito id : " . $carrito->IdCarrito);
        	return $carrito;
        }

  	}

  	/*
  		Obtiene el carrito activo del cliente.
  		Si no, retorna false.
  	 */
  	function getCarrito($idCliente){
  		$sql = "select * from carrito where IdCliente = :id and activo = TRUE";
	    try {
	        $db = getConnection();
            $stmt = $db->prepare($sql); 
	        $stmt->bindParam("id", $idCliente);
	        $stmt->execute();
	        $carrito = $stmt->fetch(PDO::FETCH_OBJ);
	        $db = null;
	        
	        if($carrito){
	        	return $carrito;
	        }
	    } catch(PDOException $e) {
	        wlog("getCarrito :: ERROR :: ". $e->getMessage() );
	        return false;
	    }
	    return false;
  	}

  	/*
  		Obtiene el carrito activo del cliente.
  		Si no, retorna false.
  	 */
  	function getCarritoById($idCarrito){
  		$sql = "select * from carrito where IdCarrito = :id";
	    try {
	        $db = getConnection();
            $stmt = $db->prepare($sql); 
	        $stmt->bindParam("id", $idCarrito);
	        $stmt->execute();
	        $carrito = $stmt->fetch(PDO::FETCH_OBJ);
	        $db = null;
	        
	        if($carrito){
	        	return $carrito;
	        }
	    } catch(PDOException $e) {
	        wlog("getCarritoById :: ERROR :: ". $e->getMessage() );
	        return false;
	    }
	    return false;
  	}

  	/*
  		Genera un nuevo carrito para un Cliente.
  		Retorna el mismo en forma de objeto.
  	 */
  	function nuevoCarrito($idCliente){
  		$fecha = date("Y-m-d");
    	$sql = "INSERT INTO `carrito` (`IdCarrito`, `IdCliente`, `Fecha`, `CantArt`, `MontoTotal`, `Activo`) ".
    			"VALUES (NULL, '".$idCliente."', '". $fecha ."', '0', '0', '1');";

    	wlog("nuevoCarrito :: SQL : " . $sql);

    	try{
    		wlog("nuevoCarrito :: Se intenta crear carrito para Cliente id : " . $idCliente);
    		$db = getConnection(); 
	        $stmt = $db->query($sql);
	        $db = null;

	        //Suponemos que para este punto el carrito ya fue creado.
	        return getCarrito($idCliente);

    	}catch(PDOException $e){
    		wlog ( "nuevoCarrito :: ERROR :: ".$e->getMessage() );
    		return false;
    	}
	}


	/*
		Obtiene una lista de articulos que cumplan 
		con los filtros establecidos.
	 */
	function filtrarArticulos($query,$idCategoria,$desde,$hasta){
		

		$articulos = null;

		if( strlen($query) > 0 ){
  			$query = "'%".$query."%'";
  		}else{
  			$query = "'%'";
  		}
  		  		
	    $sql = "select * FROM articulo WHERE nombre LIKE " . $query;
	    if($idCategoria != 0){
	    	$sql = $sql . " AND IdCategoria = " . $idCategoria;
	    }
	    if( is_numeric($desde) && $desde >= 0 ){
	    	$sql = $sql . " AND Precio >= " . $desde;	
	    } 
		if( is_numeric($hasta) && $hasta > 0){
	    	$sql = $sql . " AND Precio <= " . $hasta;	
	    }	    
	    try {
	        $db = getConnection();
            $stmt = $db->query($sql);
	        $articulos = $stmt->fetchAll(PDO::FETCH_OBJ);
	        $db = null;	       
	    } catch(PDOException $e) {
	        wlog("ERROR :: ". $e->getMessage().'-'.$sql.'-' );
	        echo "<br>";
	        echo $desde;
	        return null;
	    }

	    return $articulos;
	}

	/*
		Obtiene la lista completa de articulos
		de la BD.
	 */
	function getArticulos(){
		$articulos = null;  		
	    $sql = "select * FROM articulo ORDER BY nombre";
	    try {
	        $db = getConnection(); 
	        $stmt = $db->query($sql);
	        $articulos = $stmt->fetchAll(PDO::FETCH_OBJ);
	        $db = null;
	    } catch(PDOException $e) {
	        wlog("ERROR :: ". $e->getMessage() );
	        return $articulos;
	    }
	    return $articulos;
	}


	/*
		Obtiene un articulo a traves
		de su ID.
	 */
	function getArticulo($id){
		$articulo = null;
		$sql = "SELECT * FROM articulo WHERE IdArticulo = :id";
	    
	    wlog("getArticulo :: SQL :: " . $sql);

	    try {
	        $db = getConnection(); 
	        $stmt = $db->prepare($sql); 
	        $stmt->bindParam("id", $id);
	        $stmt->execute();
	        $articulo = $stmt->fetch(PDO::FETCH_OBJ);
	        $db = null;

	    } catch(PDOException $e) {
	        wlog("getArticulo:: ERROR :: ". $e->getMessage() );
	        return null;
	    }

	    return $articulo;
	}

	/* 
		Obtiene el listado de Categorias
	*/
	function getCategorias(){
		$categorias = null;
	    $sql = "select * FROM categoria ORDER BY nombre";
	    try {
	        $db = getConnection(); 
	        $stmt = $db->query($sql);
	        $categorias = $stmt->fetchAll(PDO::FETCH_OBJ);
	        $db = null;	      
	    } catch(PDOException $e) {
	        wlog("ERROR :: ". $e->getMessage() );
	        
	    }
	    return $categorias;
	}


	function enviarMail($carrito){
		// $mensaje = "Usted a realizado una compra por un total de " . $carrito->MontoTotal ." en GADGETSTORE";

		// $to = "oseniaes@gmail.com";
		// $subject = "Confirmacion Compra GadgetStore";
		// $message = $mensaje;
		// $from = "someonelse@example.com";
		// $headers = "From: $from";
		// mail($to,$subject,$message,$headers);
	}
  	/*
  		Se encarga de devolvere un puntero 
  		a la conexion de BD.
  	 */
	function getConnection() {
	    $dbhost="127.0.0.1";

	    //$dbuser="marcossanabria";
	    //$dbpass="admin";
	    
	    $dbuser="root";
	    $dbpass="pepe";
	    


	    $dbname="gadgetstore";	   
	    
	    $dbh = new PDO("mysql:host=$dbhost;dbname=$dbname;charset=utf8", $dbuser, $dbpass);
	    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	    return $dbh;
	}


	/*
		Dado un id de carrito retornamos sus detalles
	 */
	function getDetalleCarrito($id){

	    $sql = "SELECT * FROM detallecarrito JOIN articulo ON detallecarrito.IdArticulo = articulo.IdArticulo" 
	           ." WHERE detallecarrito.IdCarrito =:id";
	    
	    wlog("getDetalleCarrito :: SQL :: " . $sql);

	    try {
	        $db = getConnection(); 
	        $stmt = $db->prepare($sql); 
	        $stmt->bindParam("id", $id);
	        $stmt->execute();
	        $articulos= $stmt->fetchAll(PDO::FETCH_OBJ);
	        $db = null;


	        
	        //echo '{"Secretos": ' . json_encode($secreto) . '}';
	    } catch(PDOException $e) {
	        wlog("getDetalleCarrito:: ERROR :: ". $e->getMessage() );
	        return null;
	    }

	    return $articulos;
	}

	/*
		Pequena funcion para utilizar como log.
	 */
	function wlog( $mensaje ){

		$archivo = 'log.txt';
		$puntero = fopen($archivo, 'a') or die('No se pudo abrir el archivo'.$archivo);

		$entrada = "[".date("Y-m-d H:i:s")."]    " . $mensaje ."\n"; 

		fwrite($puntero, $entrada);
		fclose($puntero);
	}

?>