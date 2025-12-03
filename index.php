<?php
// =========================================================
// INDEX - ROUTEUR PRINCIPAL DU SITE SUPPORTINI.TN
// Rôle : Contrôleur central (C de MVC)
// =========================================================

require_once __DIR__ . '/model/Sujet.php';
require_once __DIR__ . '/model/Commentaire.php';
require_once __DIR__ . '/controller/sujetController.php';
require_once __DIR__ . '/controller/commentaireController.php';

// --- Initialisation des managers (Modèle) ---
$crud            = new SujetCRUD();
$commentaireCrud = new CommentaireCRUD();

// --- Détection de l'action demandée ---
$action = isset($_GET['action']) ? $_GET['action'] : 'front_liste';

$message = "";
$erreur  = "";

// =========================================================
//  FONCTIONS UTILITAIRES
// =========================================================

// Récupérer une valeur POST avec valeur par défaut
function getPost($key, $default = "")
{
    return isset($_POST[$key]) ? $_POST[$key] : $default;
}

// Récupérer une valeur GET avec valeur par défaut
function getGet($key, $default = "")
{
    return isset($_GET[$key]) ? $_GET[$key] : $default;
}

// Afficher la liste des sujets côté FRONT avec pagination
function afficherListeFront($crud, $page = 1, $parPage = 10, $erreur = "", $message = "")
{
    $page = (int) $page;
    if ($page < 1) {
        $page = 1;
    }

    $totalSujets = $crud->countAll();
    $totalPages  = 1;
    if ($parPage > 0 && $totalSujets > 0) {
        $totalPages = ceil($totalSujets / $parPage);
    }

    if ($page > $totalPages) {
        $page = $totalPages;
    }

    $offset = ($page - 1) * $parPage;
    $liste  = $crud->getPage($parPage, $offset);

    // Les variables $liste, $page, $totalPages, $parPage, $erreur, $message
    // seront utilisées dans la vue
    include __DIR__ . '/view/front_office/listeSujets.php';
}

// Afficher la liste des sujets côté BACK avec pagination
function afficherListeBack($crud, $page = 1, $parPage = 10, $erreur = "", $message = "")
{
    $page = (int) $page;
    if ($page < 1) {
        $page = 1;
    }

    $totalSujets = $crud->countAll();
    $totalPages  = 1;
    if ($parPage > 0 && $totalSujets > 0) {
        $totalPages = ceil($totalSujets / $parPage);
    }

    if ($page > $totalPages) {
        $page = $totalPages;
    }

    $offset = ($page - 1) * $parPage;
    $liste  = $crud->getPage($parPage, $offset);

    include __DIR__ . '/view/back_office/listeSujetsBO.php';
}

// ===============================
//  Fonction upload image sujet
// ===============================
function uploadImageSujet($inputName, &$erreur)
{
    $imagePath = null;

    // Si aucun fichier envoyé => on ne fait rien
    if (!isset($_FILES[$inputName])) {
        return null;
    }

    if (!isset($_FILES[$inputName]['name']) || $_FILES[$inputName]['name'] == "") {
        return null;
    }

    // Dossier physique (sur le serveur)
    $uploadDir = __DIR__ . '/uploads/sujets/';
    // Chemin WEB (pour le navigateur)
    $uploadWebDir = '/novombre/uploads/sujets/';

    // Créer le dossier s'il n'existe pas
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $fileName = basename($_FILES[$inputName]['name']);
    $fileTmp  = $_FILES[$inputName]['tmp_name'];

    // Extension du fichier
    $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

    // Formats autorisés
    if (
        $extension != 'jpg' &&
        $extension != 'jpeg' &&
        $extension != 'png' &&
        $extension != 'gif'
    ) {
        $erreur = "Format d'image non supporté (jpg, jpeg, png, gif).";
        return null;
    }

    // Nouveau nom unique
    $newName  = time() . '_' . $fileName;
    $fullPath = $uploadDir . $newName;

    // Déplacement du fichier
    if (!move_uploaded_file($fileTmp, $fullPath)) {
        $erreur = "Erreur lors de l'upload de l'image.";
        return null;
    }

    // Chemin stocké en base (chemin web)
    $imagePath = $uploadWebDir . $newName;

    return $imagePath;
}

// =========================================================
// *********  F R O N T    O F F I C E   *********
// =========================================================

// ---------- Ajouter un sujet ----------
if ($action == 'front_ajouter') {

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {

        $titre     = getPost('titre');
        $categorie = getPost('categorie');

        // Vérification des champs texte
        if ($titre == "" || $categorie == "") {
            $erreur = "Titre et catégorie sont obligatoires.";
        } else {
            // Upload image (facultatif)
            $imagePath = uploadImageSujet('image', $erreur);

            // S'il n'y a pas eu d'erreur d'upload
            if ($erreur == "") {
                $sujet = new Sujet($titre, $categorie);
                $sujet->setImage($imagePath); // peut être NULL
                $crud->add($sujet);
                $message = "Sujet ajouté avec succès.";
            }
        }

        // Après ajout (ou erreur) : on revient à la liste front, page 1
        afficherListeFront($crud, 1, 10, $erreur, $message);
        exit;
    }
}

// ---------- Détail d’un sujet ----------
if ($action == 'front_detail') {

    $id_sujet = getGet('id_sujet', null);

    if ($id_sujet) {

        $sujet_data = $crud->getById($id_sujet);

        if ($sujet_data) {

            // ===== PAGINATION COMMENTAIRES =====
            $parPageComm = 5;

            if (isset($_GET['page_commentaire'])) {
                $pageComm = (int) $_GET['page_commentaire'];
            } else {
                $pageComm = 1;
            }

            if ($pageComm < 1) {
                $pageComm = 1;
            }

            $totalCommentaires = $commentaireCrud->countBySujet($id_sujet);

            $totalPagesComm = 1;
            if ($parPageComm > 0 && $totalCommentaires > 0) {
                $totalPagesComm = ceil($totalCommentaires / $parPageComm);
            }

            if ($pageComm > $totalPagesComm) {
                $pageComm = $totalPagesComm;
            }

            $offsetComm = ($pageComm - 1) * $parPageComm;

            $liste_commentaires = $commentaireCrud->getPageBySujet(
                $id_sujet,
                $parPageComm,
                $offsetComm
            );

            include __DIR__ . '/view/front_office/detailSujet.php';

        } else {

            $erreur = "Sujet introuvable.";

            // On revient à la liste front, page 1
            afficherListeFront($crud, 1, 10, $erreur, $message);
        }
    }
    exit;
}

// ---------- Ajouter un commentaire ----------
if ($action == 'front_ajouter_commentaire') {

    $id_sujet = getPost('id_sujet');
    $contenu  = getPost('contenu');

    if ($id_sujet == "" || $contenu == "") {
        $erreur = "Le commentaire est obligatoire.";
    } else {
        $commentaire = new Commentaire($contenu, $id_sujet);
        $commentaireCrud->add($commentaire);
        $message = "Commentaire ajouté avec succès.";
    }

    $sujet_data = $crud->getById($id_sujet);

    // Après ajout : page 1 des commentaires
    $parPageComm = 5;
    $pageComm    = 1;

    $totalCommentaires = $commentaireCrud->countBySujet($id_sujet);

    $totalPagesComm = 1;
    if ($parPageComm > 0 && $totalCommentaires > 0) {
        $totalPagesComm = ceil($totalCommentaires / $parPageComm);
    }

    $offsetComm = 0;

    $liste_commentaires = $commentaireCrud->getPageBySujet(
        $id_sujet,
        $parPageComm,
        $offsetComm
    );

    include __DIR__ . '/view/front_office/detailSujet.php';
    exit;
}

// ---------- Formulaire modification commentaire ----------
if ($action == 'front_edit_form_commentaire') {

    $id_commentaire = getGet('id_commentaire');
    $id_sujet       = getGet('id_sujet');

    if ($id_commentaire != "") {

        $commentaire_data = $commentaireCrud->getById($id_commentaire);

        if ($commentaire_data) {
            include __DIR__ . '/view/front_office/editCommentaire.php';
        } else {
            $erreur     = "Commentaire introuvable.";
            $sujet_data = $crud->getById($id_sujet);

            // On revient au détail avec pagination page 1
            $parPageComm       = 5;
            $pageComm          = 1;
            $totalCommentaires = $commentaireCrud->countBySujet($id_sujet);

            $totalPagesComm = 1;
            if ($parPageComm > 0 && $totalCommentaires > 0) {
                $totalPagesComm = ceil($totalCommentaires / $parPageComm);
            }

            $offsetComm = 0;

            $liste_commentaires = $commentaireCrud->getPageBySujet(
                $id_sujet,
                $parPageComm,
                $offsetComm
            );

            include __DIR__ . '/view/front_office/detailSujet.php';
        }
    }
    exit;
}

// ---------- Modifier commentaire ----------
if ($action == 'front_modifier_commentaire') {

    $id_commentaire = getPost('id_commentaire');
    $id_sujet       = getPost('id_sujet');
    $contenu        = getPost('contenu');

    if ($contenu == "") {
        $erreur = "Le commentaire est obligatoire.";
    } else {
        $commentaire = new Commentaire($contenu, $id_sujet, $id_commentaire);
        $commentaireCrud->update($commentaire);
        $message = "Commentaire modifié.";
    }

    $sujet_data = $crud->getById($id_sujet);

    // Après modification : page 1 des commentaires
    $parPageComm       = 5;
    $pageComm          = 1;
    $totalCommentaires = $commentaireCrud->countBySujet($id_sujet);

    $totalPagesComm = 1;
    if ($parPageComm > 0 && $totalCommentaires > 0) {
        $totalPagesComm = ceil($totalCommentaires / $parPageComm);
    }

    $offsetComm = 0;

    $liste_commentaires = $commentaireCrud->getPageBySujet(
        $id_sujet,
        $parPageComm,
        $offsetComm
    );

    include __DIR__ . '/view/front_office/detailSujet.php';
    exit;
}

// ---------- Supprimer commentaire ----------
if ($action == 'front_supprimer_commentaire') {

    $id_commentaire = getGet('id_commentaire');
    $id_sujet       = getGet('id_sujet');

    if ($id_commentaire != "") {
        $commentaireCrud->delete($id_commentaire);
        $message = "Commentaire supprimé.";
    }

    $sujet_data = $crud->getById($id_sujet);

    // Après suppression : page 1 des commentaires
    $parPageComm       = 5;
    $pageComm          = 1;
    $totalCommentaires = $commentaireCrud->countBySujet($id_sujet);

    $totalPagesComm = 1;
    if ($parPageComm > 0 && $totalCommentaires > 0) {
        $totalPagesComm = ceil($totalCommentaires / $parPageComm);
    }

    $offsetComm = 0;

    $liste_commentaires = $commentaireCrud->getPageBySujet(
        $id_sujet,
        $parPageComm,
        $offsetComm
    );

    include __DIR__ . '/view/front_office/detailSujet.php';
    exit;
}

// =========================================================
// *********  B A C K   O F F I C E   *********
// =========================================================

// ---------- Supprimer sujet ----------
if ($action == 'back_supprimer') {

    $id_sujet = getGet('id_sujet');

    if ($id_sujet != "") {
        $crud->delete($id_sujet);
    }

    header("Location: /novombre/index.php?action=back_liste");
    exit;
}

// ---------- Form modification sujet ----------
if ($action == 'back_edit_form') {

    $id_sujet = getGet('id_sujet');

    if ($id_sujet != "") {
        $sujet_data = $crud->getById($id_sujet);

        if ($sujet_data) {
            include __DIR__ . '/view/back_office/editSujet.php';
        } else {
            $erreur = "Sujet introuvable.";

            // Retour à la liste back, page 1
            afficherListeBack($crud, 1, 10, $erreur, $message);
        }
    }
    exit;
}

// ---------- Modifier sujet ----------
if ($action == 'back_modifier') {

    $id_sujet  = getPost('id_sujet');
    $titre     = getPost('titre');
    $categorie = getPost('categorie');

    // On récupère l'ancienne image pour la conserver si aucune nouvelle image n'est envoyée
    $image       = null;
    $sujetAncien = null;

    if ($id_sujet != "") {
        $sujetAncien = $crud->getById($id_sujet);
        if ($sujetAncien && isset($sujetAncien['image'])) {
            $image = $sujetAncien['image'];
        }
    }

    if ($titre == "" || $categorie == "") {

        $erreur     = "Titre et catégorie obligatoires.";
        $sujet_data = $crud->getById($id_sujet);
        include __DIR__ . '/view/back_office/editSujet.php';

    } else {

        // Tenter upload d'une nouvelle image
        $nouvelleImage = uploadImageSujet('image', $erreur);

        if ($erreur == "") {

            // Si une nouvelle image a été uploadée, on remplace
            if ($nouvelleImage != null) {
                $image = $nouvelleImage;
            }

            $sujet = new Sujet($titre, $categorie, $id_sujet);
            $sujet->setImage($image);
            $crud->update($sujet);
        } else {
            // En cas d'erreur d'upload, on reste sur le form
            $sujet_data = $crud->getById($id_sujet);
            include __DIR__ . '/view/back_office/editSujet.php';
            exit;
        }
    }

    header("Location: /novombre/index.php?action=back_liste");
    exit;
}

// ---------- Liste BACK avec pagination ----------
if ($action == 'back_liste') {

    $page = (int) getGet('page', 1);

    afficherListeBack($crud, $page, 10, $erreur, $message);
    exit;
}

// ---------- Liste FRONT avec pagination ----------
if ($action == 'front_liste') {

    $page = (int) getGet('page', 1);

    afficherListeFront($crud, $page, 10, $erreur, $message);
    exit;
}

// =========================================================
// *****  PAR DÉFAUT : LISTE FRONT *****
// =========================================================

afficherListeFront($crud, 1, 10, $erreur, $message);

?>
