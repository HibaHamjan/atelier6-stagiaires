
<?php

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../config/db.php';

try {

    // Connexion à la base de données
    $pdo = getDatabase();

    // Récupérer la liste des stagiaires
    $sql = "SELECT id, nom, prenom, ville, photo, date_inscription
            FROM stagiaires
            ORDER BY id DESC";

    $stmt = $pdo->query($sql);

    $stagiaires = $stmt->fetchAll();

    // Retourner les données en JSON
    echo json_encode(
        $stagiaires,
        JSON_UNESCAPED_UNICODE
    );

} catch (Throwable $e) {
    http_response_code(500);

    echo json_encode([
        'success' => false,
        'message' => 'Erreur lors de la récupération des stagiaires.'
    ], JSON_UNESCAPED_UNICODE);
}
