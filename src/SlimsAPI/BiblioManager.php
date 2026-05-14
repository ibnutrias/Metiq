<?php
namespace Metiq\SlimsAPI;

class BiblioManager {
    private $dbs;

    public function __construct() {
        global $dbs;
        $this->dbs = $dbs;
    }

    public function getTotalBiblio() {
        $query = $this->dbs->query("SELECT COUNT(biblio_id) as total FROM biblio");
        $result = $query->fetch_assoc();
        return (int) $result['total'];
    }

    public function getBiblioChunk($limit, $offset) {
        $sql = "SELECT b.biblio_id, b.title, b.sor, b.edition, b.isbn_issn, b.publisher_id, 
                       b.publish_year, b.collation, b.series_title, b.call_number, b.language_id, 
                       b.publish_place_id, b.classification, b.notes, b.image, b.gmd_id, 
                       b.labels, b.frequency_id, b.content_type_id, b.media_type_id, b.carrier_type_id,
                       (SELECT COUNT(author_id) FROM biblio_author WHERE biblio_id = b.biblio_id) as author_count,
                       (SELECT COUNT(topic_id) FROM biblio_topic WHERE biblio_id = b.biblio_id) as subject_count
                FROM biblio b 
                ORDER BY b.biblio_id ASC 
                LIMIT {$limit} OFFSET {$offset}";
                
        $query = $this->dbs->query($sql);
        $data = [];
        while ($row = $query->fetch_assoc()) {
            $data[] = $row;
        }
        return $data;
    }

    public function getBiblioById($id) {
        // PERBAIKAN: Mengubah mt.topic_name menjadi mt.topic sesuai skema database SLiMS
        $sql = "SELECT b.biblio_id, b.title, b.sor, b.edition, b.isbn_issn, b.publish_year, 
                       b.collation, b.series_title, b.call_number, b.classification, b.notes, b.image, b.labels,
                       p.publisher_name, pl.place_name, g.gmd_name,
                       (SELECT GROUP_CONCAT(ma.author_name SEPARATOR '; ') 
                        FROM biblio_author ba JOIN mst_author ma ON ba.author_id = ma.author_id 
                        WHERE ba.biblio_id = b.biblio_id) AS author_names,
                       (SELECT GROUP_CONCAT(mt.topic SEPARATOR '; ') 
                        FROM biblio_topic bt JOIN mst_topic mt ON bt.topic_id = mt.topic_id 
                        WHERE bt.biblio_id = b.biblio_id) AS topic_names
                FROM biblio b
                LEFT JOIN mst_publisher p ON b.publisher_id = p.publisher_id
                LEFT JOIN mst_place pl ON b.publish_place_id = pl.place_id
                LEFT JOIN mst_gmd g ON b.gmd_id = g.gmd_id
                WHERE b.biblio_id = ?";
                
        $stmt = $this->dbs->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_assoc();
        $stmt->close();
        return $data;
    }

    public function updateBiblio($id, $data) {
        $sql = "UPDATE biblio SET title = ?, sor = ?, publish_year = ?, isbn_issn = ?, classification = ? WHERE biblio_id = ?";
        $stmt = $this->dbs->prepare($sql);
        $stmt->bind_param("sssssi", $data['title'], $data['sor'], $data['publish_year'], $data['isbn_issn'], $data['classification'], $id);
        $success = $stmt->execute();
        $stmt->close();
        return $success;
    }
}