<?php

class GestionnaireDocuments {
    protected $repertoire;

    public function __construct($repertoire) {
        $this->repertoire = $repertoire;
    }

    public function afficherContenuDocument($nomFichier) {
        $cheminFichier = $this->repertoire . '/' . $nomFichier;

        // Vérifier si le fichier existe
        if (file_exists($cheminFichier)) {
            // Vérifier si c'est un fichier texte
            if (pathinfo($cheminFichier, PATHINFO_EXTENSION) === 'txt') {
                // Lire le contenu du fichier
                $contenu = file_get_contents($cheminFichier);
                // Afficher le contenu
                echo nl2br($contenu); // nl2br pour conserver les sauts de ligne
            } else {
                echo "Le fichier n'est pas un fichier texte.";
            }
        } else {
            echo "Le fichier n'existe pas.";
        }
    }

    public function afficherListeDocuments() {
        $documents = scandir($this->repertoire);
        $documents = array_diff($documents, ['.', '..']);

        echo "<ul>";
        foreach ($documents as $document) {
            $nomDocumentSansExtension = pathinfo($document, PATHINFO_FILENAME);
            $nomDocumentCapitalise = ucfirst($nomDocumentSansExtension);
            echo "<li><a href='docs/$document'>$nomDocumentCapitalise</a></li>";
        }
        echo "</ul>";
    }
}

?>
