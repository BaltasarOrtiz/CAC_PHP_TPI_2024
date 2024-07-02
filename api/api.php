<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type");

include 'db.php';
include 'Peliculas.php';

$method = $_SERVER['REQUEST_METHOD'];
$action = isset($_GET['action']) ? $_GET['action'] : null; // Verifica si 'action' está definido

switch ($method) {
    case 'GET': 
        if ($action == 'createTable') {
            createTable($conn);
            echo json_encode(['message' => 'Tabla creada correctamente']);
        } else if ($action == 'cargarPeliculas'){
            cargarPeliculas($conn);
            echo json_encode(['message' => 'Peliculas cargadas correctamente']);
        } else {
            handleGet($conn);
        }
        break;
    case 'POST':
        handlePost($conn);
        break;
    case 'PUT':
        handlePut($conn);
        break;
    case 'DELETE':        
        handleDelete($conn);
        break;
    default:
        echo json_encode(['message' => 'Método no permitido']);
        break;
}

function createTable($conn){
    $sql = "CREATE TABLE IF NOT EXISTS peliculas (
        id INT AUTO_INCREMENT PRIMARY KEY,
        titulo VARCHAR(255) NOT NULL,
        fecha_lanzamiento DATE NOT NULL,
        genero VARCHAR(255) NOT NULL,            
        duracion INT,
        director VARCHAR(255),
        reparto TEXT,
        sinopsis TEXT
    )";
    $conn->exec($sql);
}

function cargarPeliculas($conn){
    $sql = "INSERT INTO peliculas (titulo, fecha_lanzamiento, genero, duracion, director, reparto, sinopsis) VALUES
    ('El Padre', '2021-02-26', 'Drama', 97, 'Florian Zeller', 'Anthony Hopkins, Olivia Colman, Mark Gatiss, Olivia Williams, Imogen Poots, Rufus Sewell', 'Un hombre mayor que vive solo en Londres y rechaza toda ayuda de sus hijas, desconfiado y algo testarudo, empieza a experimentar un cambio en su salud mental que alterará su vida por completo.'),
    ('Nomadland', '2021-02-19', 'Drama', 107, 'Chloé Zhao', 'Frances McDormand, David Strathairn, Linda May, Swankie, Bob Wells, Derek Endres', 'Tras el colapso económico de una ciudad en la zona rural de Nevada, Fern (Frances McDormand) carga sus cosas en una van y emprende la vida como nómada en la carretera.'),
    ('Sound of Metal', '2021-01-29', 'Drama', 120, 'Darius Marder', 'Riz Ahmed, Olivia Cooke, Paul Raci, Lauren Ridloff, Mathieu Amalric, Tom Kemp', 'Ruben (Riz Ahmed) y Lou (Olivia Cooke) forman un dúo de heavy metal que recorre el país en su autocaravana. De repente, Ruben pierde la audición y su vida cambia por completo.'),
    ('El juicio de los 7 de Chicago', '2020-10-16', 'Drama', 130, 'Aaron Sorkin', 'Eddie Redmayne, Alex Sharp, Sacha Baron Cohen, Jeremy Strong, John Carroll Lynch, Yahya Abdul-Mateen II', 'En 1969, siete personas fueron acusadas de conspirar para incitar disturbios en la Convención Nacional Demócrata en Chicago. El juicio que siguió fue uno de los más notorios de la historia de EE. UU.'),
    ('El juicio de los 7 de Chicago', '2020-10-16', 'Drama', 130, 'Aaron Sorkin', 'Eddie Redmayne, Alex Sharp, Sacha Baron Cohen, Jeremy Strong, John Carroll Lynch, Yahya Abdul-Mateen II', 'En 1969, siete personas fueron acusadas de conspirar para incitar disturbios en la Convención Nacional Demócrata en Chicago. El juicio que siguió fue uno de los más notorios de la historia de EE. UU.'),
    ('El juicio de los 7 de Chicago', '2020-10-16', 'Drama', 130, 'Aaron Sorkin', 'Eddie Redmayne, Alex Sharp, Sacha Baron Cohen, Jeremy Strong, John Carroll Lynch, Yahya Abdul-Mateen II', 'En 1969, siete personas fueron acusadas de conspirar para incitar disturbios en la Convención Nacional Demócrata en Chicago. El juicio que siguió fue uno de los más notorios de la historia de EE. UU.');";
    $conn->exec($sql);
}

//este metodo me devuelve una pelicula o todas las peliculas
function handleGet($conn) 
{
    $id = isset($_GET['id']) ? intval($_GET['id']) : 0;

    if ($id > 0) 
    {
        $stmt = $conn->prepare("SELECT * FROM peliculas WHERE id = ?");
        $stmt->execute([$id]);
        $pelicula = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($pelicula) 
        {
            $peliculaObj = Peliculas::fromArray($pelicula);
            echo json_encode($peliculaObj->toArray());
        } 
        else 
        {
            http_response_code(404);
            echo json_encode(['message' => 'No se encontraron datos']);
        }
    } 
    else 
    {
        $stmt = $conn->query("SELECT * FROM peliculas");
        $peliculas = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $peliculaObjs = array_map(fn($pelicula) => Peliculas::fromArray($pelicula)->toArray(), $peliculas);
        echo json_encode(['peliculas' => $peliculaObjs]);
    }
}

//este metodo es para ingresar peliculas
function handlePost($conn) 
{
    if ($conn === null) 
    {
        echo json_encode(['message' => 'Error en la conexión a la base de datos']);
        return;
    }

    $data = json_decode(file_get_contents('php://input'), true);

    $requiredFields = ['titulo', 'fecha_lanzamiento','genero' ];
    foreach ($requiredFields as $field) 
    {
        if (!isset($data[$field])) 
        {
            echo json_encode(['message' => 'Datos de la película incompletos']);
            return;
        }
    }

    $pelicula = Peliculas::fromArray($data);

    try 
    {
        $stmt = $conn->prepare("INSERT INTO peliculas (titulo, fecha_lanzamiento, genero, duracion, director, reparto, sinopsis) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $pelicula->titulo,
            $pelicula->fecha_lanzamiento,
            $pelicula->genero,
            $pelicula->duracion,
            $pelicula->director,
            $pelicula->reparto,
            $pelicula->sinopsis
           
        ]);

        echo json_encode(['message' => 'Película ingresada correctamente']);
    } 
    catch (PDOException $e) 
    {
        echo json_encode(['message' => 'Error al ingresar la película', 'error' => $e->getMessage()]);
    }
}

function handlePut($conn) 
{
    $id = isset($_GET['id']) ? intval($_GET['id']) : 0;

    if ($id > 0) 
    {
        $data = json_decode(file_get_contents('php://input'), true);
        $pelicula = Peliculas::fromArray($data);
        $pelicula->id = $id;

        $fields = [];
        $params = [];

        if ($pelicula->titulo !== null) {
            $fields[] = 'titulo = ?';
            $params[] = $pelicula->titulo;
        }
        if ($pelicula->genero !== null) {
            $fields[] = 'genero = ?';
            $params[] = $pelicula->genero;
        }
        if ($pelicula->fecha_lanzamiento !== null) {
            $fields[] = 'fecha_lanzamiento = ?';
            $params[] = $pelicula->fecha_lanzamiento;
        }
        if ($pelicula->duracion !== null) {
            $fields[] = 'duracion = ?';
            $params[] = $pelicula->duracion;
        }
        if ($pelicula->director !== null) {
            $fields[] = 'director = ?';
            $params[] = $pelicula->director;
        }
        if ($pelicula->reparto !== null) {
            $fields[] = 'reparto = ?';
            $params[] = $pelicula->reparto;
        }
        if ($pelicula->sinopsis !== null) {
            $fields[] = 'sinopsis = ?';
            $params[] = $pelicula->sinopsis;
        }                 

        if (!empty($fields)) 
        {
            $params[] = $id;
            $stmt = $conn->prepare("UPDATE peliculas SET " . implode(', ', $fields) . " WHERE id = ?");
            $stmt->execute($params);
            echo json_encode(['message' => 'Película actualizada con éxito']);
        } 
        else 
        {
            echo json_encode(['message' => 'No hay campos para actualizar']);
        }
    } 
    else 
    {
        echo json_encode(['message' => 'ID no proporcionado']);
    }
}


//metodo para borrar registros
function handleDelete($conn) 
{
    $id = isset($_GET['id']) ? intval($_GET['id']) : 0;

    if ($id > 0) 
    {
        $stmt = $conn->prepare("DELETE FROM peliculas WHERE id = ?");
        $stmt->execute([$id]);
        echo json_encode(['message' => 'Película eliminada con éxito']);
    } 
    else 
    {
        echo json_encode(['message' => 'ID no proporcionado']);
    }
}
?>