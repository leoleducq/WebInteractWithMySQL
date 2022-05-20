<!DOCTYPE html>
<html>
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
    //------------------Appel des fonctions--------------------
    require "module/function.php";
    //Connexion à la BDD
    $db = connect_request();
    //-------------------Préparation des requêtes------------------
    //Requête pour récuperer le nom des colonnes
    $colonnes = colonne_table($table,$db);
    //Requête pour récupérer les tuples
    $tuples = tuples($db,$table,$primary_key,$primary_value);

//-------------------------------------Exécution de la requête------------------------------------
    //Si l'utilisateur clique sur oui
    if(isset($_GET['valider']) && $_GET['valider']=="Oui" && $_GET['utf_8'] == true)
    {
        //Récupération de la chaine $set
        $set = $_GET['set'];
        try{
            //Requete d'update du tuple
            $requete = $db->prepare("UPDATE $table SET $set WHERE $primary_key = '$primary_value'");
            $requete->execute();
            echo "<p>La ligne ayant $primary_key = $primary_value a bien été modifiée</p>";
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
            if(!isset($nom_filtre))//Si aucun filtre
            {
                $nom_filtre ="";
                $value_filtre="";
            }
            header("Location: ../index.php?table=$table&nb_debut_ligne=$nb_debut_ligne&nb_lignes=$nb_lignes&showtri=$showtri&$nom_filtre=$value_filtre&primary_key=$primary_key&primary_value=$primary_value");
        }
        catch(Exeption $e){
            die('Erreur : ' . $e->GETMessage());
        }
        
    }
    if(isset($_GET['valider']) && $_GET['valider'] == "Oui" && $_GET['utf_8'] == false){
        echo "Une ou plusieurs colonnes ont des caractères non utf_8";
    }
?>
    <head>
        <meta charset="utf-8" />
        <link rel="stylesheet" href="../style/modifaddsupp.css" />
        <link rel="shortcut icon" href="#" />
        <title>MODIFIER <?php echo $text ;?></title>
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
    ?>

<body>
    <h1>Vous modifiez les données de <?php echo "$primary_key = $primary_value";?></h1>
<!---------------------------Début du tableau---------------------------------------->
<form action="" method="GET">
<table>
        <!------------Nom de colonne du tableau--------->
        <?php
        //Variable comptant le nombre de colonnes
        $cpt=0;
        //Parcourt toutes les colonnes
        foreach($colonnes as $colonne)
        {
            $colonne = $colonne['Field'];
            //Récupération des filtres et leurs valeurs
            if(isset($_GET['filtre_'.$colonne]))
            {
                $nom_filtre = "filtre_$colonne";
                $value_filtre = $_GET['filtre_'.$colonne];
            }
            //Affiche le nom de la colonne
            //Ouverture balise tr
            if($table!="callsignsroutes" || ($table=="callsignsroutes" && $colonne==$primary_key))
            {
                ?>
                <tr>
                <td id="colonne"><?php echo $colonne;?></td>
                <?php
            }
            
            //Récupère la clé primaire de la table
            if(($colonne ==$primary_key))
            {
                $primary_cpt = $cpt;
                //Parcourt les tuples
                foreach($tuples as $tuple)
                {
                    //Si le tuple est égale à la clé primaire
                    if($tuple[$cpt] == $tuple[$primary_cpt])
                    {
                        //Impossible de le modifier
                        ?>
                        <td><?php echo $tuple[$cpt]; ?></td>
                        <?php
                    }
                }
            }
            //----------------------SI AIRCRAFTS----------------------
            if(($table=="aircrafts" || $table=="callsignsroutes")&&($colonne != $primary_key))
            {
                //-----Ouverture du fichier des dépendances------
                //Nom du fichier
                $filename = "specific_rules";
                //Ouverture du fichier en mode lecture
                $fp = fopen($filename, "r");
                //Parcourt les lignes une à une
                while (($line = fgets($fp)) !== false)
                {
                    //Récupère le nom de la colonne
                    $search_colonne = strtok($line, '=');
                    //Si le nom de la colonne correspond
                    if($search_colonne == $colonne)
                    {
                        //Permet de chercher si il y a des dependances liés à cette colonne
                        $valide_aircrafts = strpos($line,",");
                        //Si la valeur display est sur la colonne alors on l'affiche
                        $valide_callsignsroutes = strpos($line,"display");
                        //Si il y a une virgule dans la ligne alors valide est oui
                        if(gettype($valide_aircrafts) =="integer"){$valide_aircrafts="oui";}
                        if(gettype($valide_callsignsroutes) =="integer"){$valide_callsignsroutes="oui";}
                        //Récupère la table dans laquelle aller chercher les informations
                        $find_table = strtok($line,",");//Prend ce qu'il y a avant la virgule
                        $find_table = str_replace("$colonne=table:","",$find_table);//Retire le debut et donc ne garde que la table
                        //Récupère la colonne de la table dans laquelle aller chercher les informations
                        $find_colonne = substr(str_replace("$colonne=table:$find_table,colonne:","",$line),0,-1);
                        $valide = substr($find_colonne, -1);
                        //Boucle pour itérer sur chaque tuple
                        foreach($tuples as $tuple)
                        {
                            if($table=="aircrafts")//Si table Aicrafts
                            {
                                if($colonne =="updated")//Si colonne updated et table Aircrafts
                                {
                                    $updated = date("Y-m-d H:i:s");//Récupère la date à l'instant t
                                    ?>
                                    <td><?php echo $updated; ?></td>
                                    <?php
                                }
                                if($colonne =="majuser")//Si colonne majuser et table Aircrafts
                                {
                                    ?>
                                    <td><?php echo $user ?></td>
                                    <?php
                                }
                                if($colonne !="updated" && $colonne !="majuser")//Toutes les autres colonnes de aircrafts
                                {
                                    ?>
                                    <td><input id="<?php echo $colonne ;?>" name="<?php echo $colonne;?>"type="text" value="<?php echo $_GET[$colonne] ?? $tuple[$cpt]; ?>">
                                    <?php
                                    if($valide_aircrafts =="oui")//Si il ya une dépendance
                                    {
                                        ?>
                                        <!-- Instanciation d'une liste -->
                                        <select id="<?php echo "liste.$colonne" ;?>" onchange="change(this)">
                                        <?php
                                        //Requête SQL pour récupérer les élements à mettre dans la liste
                                        $liste = liste_deroulante($db,$find_table,$find_colonne);
                                        foreach($liste as $element)
                                        {
                                            $element = $element['colonne'];
                                            //Si element = value input, selected element dans la liste
                                            if($_GET[$colonne] == $element || $tuple[$cpt] == $element)
                                            {
                                                ?>
                                                <option value="<?php echo $element ;?>" selected><?php echo $element ;?></option>
                                                <?php
                                            }
                                            //Sinon ajout element normalement dans la liste
                                            else
                                            {
                                                ?>
                                                <option value="<?php echo $element ;?>"><?php echo $element ;?></option>
                                                <?php
                                            }
                                        }
                                        //Fermeture de la liste
                                        ?>
                                        </select></td>
                                        <?php
                                    }//Fin : Recherche dépendances
                                }//Fin : autres colonnes Aircrafts
                            }//Fin : Table aircrafts
                            if($table =="callsignsroutes")//Si table Callsignsroutes
                            {
                                if($valide_callsignsroutes =="oui")//Affiche uniquement les colonnes nécessaires
                                {
                                    //Afficher que les colonnes nécessaires à remplir les autres automatiquement
                                    ?>
                                    <tr>
                                    <td id="colonne"><?php echo $colonne;?></td>
                                    <td><input id="<?php echo $colonne ;?>" name="<?php echo $colonne ;?>" type="text" value="<?php echo $_GET[$colonne] ?? $tuple[$cpt];?>" onkeyup="toggleFile(this);">
                                    <?php
                                }
                            }
                        }//Fin : Itération sur les tuples
                    }//Fin : condition search_colonne = colonne
                }//Fin : lecture du fichier
                fclose($fp);//Fermeture du fichier
            //----------------------SI AUTRE TABLE----------------------
            }
            else
            {
                //Si ce n'est pas la clé primaire
                if($colonne != $primary_key)
                {
                    //Itère sur chaque tuple
                    foreach($tuples as $tuple)
                    {
                        ?>
                        <!-------------Prend les valeurs de la table ou des valeurs precedemment transmises------------->
                        <td><input name="<?php echo $colonne;?>"type="text" value="<?php echo $_GET[$colonne] ?? $tuple[$cpt]; ?>"></td>
                        <?php
                    }
                }
            }
                ?>
                </tr>
                <?php
                $cpt++;
        }
        ?>

</table>
<div id="bouton">
<form action="" method="GET">
<input type="submit" name="modifier" value="Modifier">
<!---Input de type Hidden de toutes les données préalablement transmises------->
    <input type="hidden" name="table" value="<?php echo $table;?>">
    <input type="hidden" name="primary_key" value="<?php echo $primary_key;?>">
    <input type="hidden" name="primary_value" value="<?php echo $primary_value;?>">
    <input type="hidden" name="nb_debut_ligne" value="<?php echo $nb_debut_ligne;?>">
    <input type="hidden" name="nb_lignes" value="<?php echo $nb_lignes;?>">
    <input type="hidden" name="showtri" value="<?php echo $showtri;?>">
    <input type="hidden" name="<?php echo $nom_filtre;?>" value="<?php echo $value_filtre;?>">
    <?php
    if($table =="aircrafts")
    {
        ?>
        <input type="hidden" name="updated" value="<?php echo $updated;?>">
        <input type="hidden" name="majuser" value="<?php echo $user;?>">
        <?php
    }
    ?>
</form>
<form action="../index.php" method="GET">
<!---Input de type Hidden de toutes les données préalablement transmises------->
    <input type="submit" name="quitter" value="Retour">
    <input type="hidden" name="table" value="<?php echo $table;?>">
    <input type="hidden" name="nb_debut_ligne" value="<?php echo $nb_debut_ligne;?>">
    <input type="hidden" name="nb_lignes" value="<?php echo $nb_lignes;?>">
    <input type="hidden" name="showtri" value="<?php echo $showtri;?>">
    <input type="hidden" name="<?php echo $nom_filtre;?>" value="<?php echo $value_filtre;?>">
    <input type="hidden" name="primary_key" value="<?php echo $primary_key;?>">
    <input type="hidden" name="primary_value" value="<?php echo $primary_value;?>">
</form>
</div>
<!----------------------------------------------------Fin du tableau-------------------------------------------------->
<?php
    $cpt=0;
    //Quand l'utilisateur clique sur le bouton modifier
    if(isset($_GET['modifier']))
    {
        //Instanciation de la variable permmetant de voir si un caractere non utf8 est present
        $utf_8 = true;
        //Compteur de nombre de colonnes avec des valeurs non_utf_8
        $cpt_non_utf = 0;
        $non_utf8 = "";
        //Récupère la liste des infos sur les types
        $list_type = verif_type($colonnes,$table);
        //Liste des champs n'ayant pas le bon type
        $non_type = $list_type[0];
        //Nombre de champs n'ayant pas le bon type
        $cpt_non_type = $list_type[1];
        //Booléen pour voir si il y a des mauvais types
        $bool_type = $list_type[2];
        //Traitement spécial pour callsignsroutes
        if($table=="callsignsroutes")
        {
            $list_get = array("fromairporticao","fromairportiata","toairporticao","toairportiata");
            foreach($list_get as $get)
            {
                //Récupère la valeur des champs
                $value = $_GET[$get];
                //Enlève les accents
                $value = unaccent($value);
                //Si non_utf8
                if (!test_utf8($value))
                {
                    //Décode la valeur
                    $value = utf8_encode($value);
                    //Passe le parametre utf8 à false pour empecher la requete
                    $utf_8 = false;
                    //Récupère les colonnes avec des valeurs non utf_8
                    $non_utf8 .= $get.",";
                    //Compte le nombre de colonnes avec des valeurs non utf-8
                    $cpt_non_utf ++;
                }
                
            }
            $set = modifier($db,$primary_value,$_GET['fromairporticao'] ??"",$_GET['toairporticao']??"",$_GET['fromairportiata']??"",$_GET['toairportiata']??"");
        }
        else
        {
            //Instanciation de la chaine a prendre en compte dans la requete
            $set = "";
            foreach($colonnes as $colonne)
            {
                $colonne = $colonne['Field'];
                $set.=$colonne."=";
                //Si c'est la chaine de la clé primaire
                if($tuple[$cpt] == $tuple[$primary_cpt])
                {
                    //Récupère la valeur de la requête
                    $value = $tuple[$cpt];
                }
                else
                {
                    //Récupère la valeur rentré par l'utilisateur
                    $value = $_GET[$colonne];
                }
                //Enlève les accents
                $value = unaccent($value);
                //Vérification des chaines de caractères avant de les mettre dans la chaine de la requete
                if (!test_utf8($value))
                {
                    $value = utf8_encode($value);
                    $utf_8 = false;
                    $non_utf8 .= $colonne.",";
                    $cpt_non_utf ++;
                }
                $set .= "'".$value."',";
                $cpt++;
            }
        }
        //Enlève la dernière virgule de la chaine
        $set = rtrim($set, ", ");
        $non_utf8 = rtrim($non_utf8, ", ");
        //Variable pour montrer la reqûete à l'utilisateur
        $show_requete = "<b>UPDATE</b> $table <b>SET</b> $set <b>WHERE</b> $primary_key = '$primary_value'";
        ?>
        <!----Affichage du bouton pour valider la requête ou non------->
        <?php
        //Si utf_8 à false alors n'affiche pas le bouton pour valider la requête
        if($utf_8 == true && $bool_type == true)
        {
            ?>
        <div id="bouton"><p> Êtes vous sûrs de vouloir exécuter cette requête ?</p>
            <p><?php echo $show_requete ?></p>
            <form action="" method="GET">
            <!---------------- Récupération de toutes les variables----------------------->
            <input type="submit" name="valider" value="Oui">
            <input type="submit" name="valider" value="Non">
            <input type="hidden" name="table" value="<?php echo $table ;?>">
            <input type="hidden" name="primary_key" value="<?php echo $primary_key ;?>">
            <input type="hidden" name="primary_value" value="<?php echo $primary_value ;?>">
            <input type="hidden" name="nb_debut_ligne" value="<?php echo $nb_debut_ligne;?>">
            <input type="hidden" name="nb_lignes" value="<?php echo $nb_lignes;?>">
            <input type="hidden" name="showtri" value="<?php echo $showtri;?>">
            <input type="hidden" name="set" value="<?php echo $set ;?>">
            <input type="hidden" name="utf_8" value="<?php echo $utf_8 ;?>">
            <input type="hidden" name="<?php echo $nom_filtre;?>" value="<?php echo $value_filtre;?>">
            <?php
            //Renvoi des input hidden des valeurs rentrés précédemment
            foreach($colonnes as $colonne)
            {
                $colonne = $colonne['Field'];
            //Itère sur chaque colonne une à une
            ?>
            <input name="<?php echo $colonne;?>" type="hidden" value="<?php echo $_GET[$colonne] ?? "";?>">
            <?php
            }
        ?>
            </form>
        </div>
        <?php
        }
        ?>

    <?php
        //Messages d'erreurs en cas de mauvais type ou non utf_8
        if($utf_8 == false && $cpt_non_utf == 1){
            echo "<p id='erreur'>La colonne : $non_utf8 a des caractères non utf_8.</p>";
        }
        if($utf_8 == false && $cpt_non_utf > 1){
            echo "<p id='erreur'>Les colonnes : $non_utf8 ont des caractères non utf_8.</p>";
        }
        if($bool_type == false && $cpt_non_type == 1)
        {
            echo "<p id='erreur'>La colonne : $non_type a un mauvais type de valeur.</p>";
        }
        if($bool_type == false && $cpt_non_type > 1)
        {
            echo "<p id='erreur'>Les colonnes : $non_type ont des mauvais types de valeur.</p>";
        }
    }
?>        <!--Appel du code javascript--> 
        <script src="../script/main.js"></script>
        
</body>

</html>