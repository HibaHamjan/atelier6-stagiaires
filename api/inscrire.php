<?php

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/db.php';

use Cloudinary\Cloudinary;

try {

    // Vérifier la méthode
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Méthode non autorisée.');
    }

    // Récupérer les données
    $nom = trim($_POST['nom'] ?? '');
    $prenom = trim($_POST['prenom'] ?? '');
    $ville = trim($_POST['ville'] ?? '');

    // Vérifier les champs
    if ($nom === '' || $prenom === '' || $ville === '') {
        throw new Exception('Tous les champs sont obligatoires.');
    }

    // Vérifier la photo
    if (
        !isset($_FILES['photo']) ||
        $_FILES['photo']['error'] !== UPLOAD_ERR_OK
    ) {
        throw new Exception('Veuillez choisir une photo valide.');
    }

    $photo = $_FILES['photo'];

    if ($photo['size'] > 5 * 1024 * 1024) {
        throw new Exception('La photo dépasse 5 Mo.');
    }

    // Vérifier le format réel de la photo
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($photo['tmp_name']);

    $allowedTypes = [
        'image/jpeg',
        'image/png',
        'image/webp'
    ];

    if (!in_array($mime, $allowedTypes, true)) {
        throw new Exception('Format de photo non accepté.');
    }

    // Configuration Cloudinary
    $cloudinaryUrl = getenv('CLOUDINARY_URL');

    if (!$cloudinaryUrl) {
        throw new Exception('Configuration Cloudinary manquante.');
    }

    $caPath = dirname(__DIR__) . '/certs/cacert.pem';

if (!file_exists($caPath)) {
    throw new Exception('Certificat CA introuvable.');
}

$cloudinary = new Cloudinary($cloudinaryUrl);

$httpClient = new \GuzzleHttp\Client([
    'verify' => $caPath,
    'timeout' => 30,
    'connect_timeout' => 10
]);

$cloudinary->setHttpClient($httpClient);

    // Envoyer la photo vers Cloudinary
    $result = $cloudinary->uploadApi()->upload(
        $photo['tmp_name'],
        [
            'folder' => 'stagiaires'
        ]
    );

    $photoUrl = $result['secure_url'];

    // Connexion à la base de données
    $pdo = getDatabase();

    // Enregistrer le stagiaire
    $sql = "INSERT INTO stagiaires (nom, prenom, ville, photo)
            VALUES (:nom, :prenom, :ville, :photo)";

    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        ':nom' => $nom,
        ':prenom' => $prenom,
        ':ville' => $ville,
        ':photo' => $photoUrl
    ]);

    echo json_encode([
        'success' => true,
        'message' => 'Inscription réussie !'
    ], JSON_UNESCAPED_UNICODE);

}   catch (Throwable $e) {
    http_response_code(400);

    echo json_encode([
        'success' => false,
        'message' => 'Une erreur est survenue lors de l’inscription.'
    ], JSON_UNESCAPED_UNICODE);
}
   