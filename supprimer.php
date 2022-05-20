<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8" />
        <link rel="stylesheet" href="../style/modifaddsupp.css" />
        <?php
            //Récupération des variables dans l'URL
            $table = $_GET['table'];
            $primary_key = $_GET['primary_key'];
            $primary_value = $_GET['primary_value'];
            $nb_debut_ligne = $_GET['nb_debut_ligne'];
            $nb_lignes = $_GET['nb_lignes'];
            $showtri = $_GET['showtri'];
            //Texte pour le titre de la page
            $text = "$table, $primary_key = $primary_value";
            ?>
        <title>SUPPRIMER <?php echo $text ;?></title>
    </head>

<?php
    //Début de la session
    session_start();
    if($_SESSION['username'] !== ""){
        $user = $_SESSION['username'];
    }
    if(empty(isset($_SESSION['username'])))
    {
        header("location:../login.php");
    }
    //------------------Appel des fonctions--------------------
    require "module/function.php";
    //Connexion à la BDD
    $db = connect_request();
    //Préparation de la requête
    $requete = $db->prepare("DELETE FROM $table WHERE $primary_key = '$primary_value'");
    //Variable pour montrer la reqûete à l'utilisateur
    $show_requete = "DELETE FROM $table WHERE $primary_key = '$primary_value'";
    //Requête pour récuperer le nom des colonnes
    $colonnes = colonne_table($table,$db);
    //Récupère le filtre
    foreach($colonnes as $colonne)
    {
        $colonne = $colonne['Field'];
        //Récupération des filtres et leurs valeurs
        if(isset($_GET['filtre_'.$colonne]))
        {
            $nom_filtre = "filtre_$colonne";
            $value_filtre = $_GET['filtre_'.$colonne];
        }
    }
    ?>

    
    <div><p> Êtes vous sûrs de vouloir exécuter cette requête ?</p>
        <p><?php echo $show_requete ?></p>
        <form action="" method="GET">
            <!---------------- Récupération de toutes les variables----------------------->
            <input type="submit" name="delete" value="Oui">
            <input type="hidden" name="table" value="<?php echo $table ;?>">
            <input type="hidden" name="primary_key" value="<?php echo $primary_key ;?>">
            <input type="hidden" name="primary_value" value="<?php echo $primary_value ;?>">
            <input type="hidden" name="nb_debut_ligne" value="<?php echo $nb_debut_ligne;?>">
            <input type="hidden" name="nb_lignes" value="<?php echo $nb_lignes;?>">
            <input type="hidden" name="showtri" value="<?php echo $showtri;?>">
            <input type="hidden" name="<?php echo $nom_filtre;?>" value="<?php echo $value_filtre;?>">
        </form>
        <!------------------Ferme la fenetre------------------------>
        <form action="../index.php" method="GET">
    <!---Input de type Hidden de toutes les données préalablement transmises------->
            <input type="submit" name="quitter" value="Annuler">
            <input type="hidden" name="table" value="<?php echo $table;?>">
            <input type="hidden" name="nb_debut_ligne" value="<?php echo $nb_debut_ligne;?>">
            <input type="hidden" name="nb_lignes" value="<?php echo $nb_lignes;?>">
            <input type="hidden" name="showtri" value="<?php echo $showtri;?>">
            <input type="hidden" name="<?php echo $nom_filtre;?>" value="<?php echo $value_filtre;?>">
        </form>
    </div>
    
    <?php
    $delete = isset($_GET['delete']) ? $_GET['delete'] : NULL;
    //Si l'utilisateur clique sur oui
    if($delete =="Oui"){
        try{
            $requete->execute();
            echo "<p>La ligne ayant $primary_key = $primary_value a bien été supprimée</p>";
            foreach($colonnes as $colonne)
            {
                $colonne = $colonne['Field'];
                //Récupération des filtres et leurs valeurs
                if(isset($_GET['filtre_'.$colonne]))
                {
                    $nom_filtre = "filtre_$colonne";
                    $value_filtre = $_GET['filtre_'.$colonne];
                }
            }
            header("Location: ../index.php?table=$table&nb_debut_ligne=$nb_debut_ligne&nb_lignes=$nb_lignes&showtri=$showtri&$nom_filtre=$value_filtre");
        }
        catch(Exception $e){
            die('Erreur : ' . $e->getMessage());
        }

    }
?>


</html>