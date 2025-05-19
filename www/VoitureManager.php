<?php

class VoitureManager {
    private $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function deleteVoiture(int $id): void {
        if (!is_int($id) || $id <= 0) {
            throw new InvalidArgumentException("ID de voiture invalide.");
        }

        $query = "DELETE FROM animes WHERE id = :id";
        $stmt = $this->pdo->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
    }

    public function searchVoitures(string $search = ''): array {
        $search = trim($search);

        if (empty($search)) {
            return $this->getVoituresWithPagination(0, 10);
        }

        $query = "SELECT animes.*, categories.name AS category_name, models.name AS model_name 
                 FROM animes
                 LEFT JOIN categories ON animes.category_id = categories.id
                 LEFT JOIN models ON animes.model_id = models.id
                 WHERE LOWER(categories.name) LIKE LOWER(:search) 
                 OR LOWER(models.name) LIKE LOWER(:search) 
                 OR LOWER(animes.description) LIKE LOWER(:search)";

        $stmt = $this->pdo->prepare($query);
        $searchTerm = '%' . $search . '%';
        $stmt->bindParam(':search', $searchTerm, PDO::PARAM_STR);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function createVoiture(string $description, int $category_id, int $model_id, string $image_data): void {
        if (empty($description) || !is_int($category_id) || !is_int($model_id) || empty($image_data)) {
            throw new InvalidArgumentException("Données de voiture invalides.");
        }

        $description = trim(htmlspecialchars($description, ENT_QUOTES, 'UTF-8'));

        $query = "INSERT INTO animes (description, image, category_id, model_id) VALUES (:description, :image, :category_id, :model_id)";
        $stmt = $this->pdo->prepare($query);
        $stmt->bindParam(':description', $description, PDO::PARAM_STR);
        $stmt->bindParam(':image', $image_data, PDO::PARAM_LOB);
        $stmt->bindParam(':category_id', $category_id, PDO::PARAM_INT);
        $stmt->bindParam(':model_id', $model_id, PDO::PARAM_INT);
        $stmt->execute();
    }

    // Nouvelle méthode : Récupérer les voitures avec pagination
    public function getVoituresWithPagination(int $offset, int $limit): array {
        $query = "SELECT animes.*, categories.name AS category_name, models.name AS model_name 
                  FROM animes
                  LEFT JOIN categories ON animes.category_id = categories.id
                  LEFT JOIN models ON animes.model_id = models.id
                  LIMIT :limit OFFSET :offset";
        $stmt = $this->pdo->prepare($query);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Nouvelle méthode : Compter le nombre total de voitures
    public function countVoitures(): int {
        $query = "SELECT COUNT(*) FROM animes";
        return (int) $this->pdo->query($query)->fetchColumn();
    }
}