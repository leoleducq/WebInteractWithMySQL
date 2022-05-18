<!DOCTYPE html>
<html>
<?php
    //Début de la session
    session_start();
    if($_SESSION['username'] !== ""){
        $user = $_SESSION['username'];
    }
    if(empty(isset($_SESSION['username'])))
    {
        header("location:login.php");
    }
    //Récupération des variables dans l'URL
    $table = $_GET['table'];
    $primary_key = $_GET['primary_key'];
    $nb_debut_ligne = $_GET['nb_debut_ligne'];
    $nb_lignes = $_GET['nb_lignes'];
    $showtri = $_GET['showtri'];
    //------------------Appel des fonctions--------------------
    require "module/function.php";
    //Connexion à la BDD
    $db = connect_request();
    //-------------------Préparation des requêtes------------------
    //Requête pour récuperer le nom des colonnes
    $colonnes = colonne_table($table,$db);
    //-------------------------------------Exécution de la requête------------------------------------
    //Si l'utilisateur clique sur oui
    if(isset($_GET['valider']) && $_GET['valider'] == "Oui" && $_GET['utf_8'] == true)
    {
        //Récupération de la chaine $add
        $add = $_GET['add'];
        try{
            
            //Requete d'insertion du tuple
            $requete = $db->prepare("INSERT INTO $table VALUES ($add)");
            $requete->execute();
            echo "<p>La ligne a bien été insérée</p>";
            //Récupère la valeur associé à la clé primaire
            $primary_value = $_GET[$primary_key];
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
            //Redirection
            header("Location: majtab.php?table=$table&nb_debut_ligne=$nb_debut_ligne&nb_lignes=$nb_lignes&showtri=$showtri&$nom_filtre=$value_filtre&primary_key=$primary_key&primary_value=$primary_value");
        }
        catch(Exeption $e){
            die('Erreur : ' . $e->getMessage());
        }
        
    }
    if(isset($_GET['valider']) && $_GET['valider'] == "Oui" && $_GET['utf_8'] == false){
        echo "Une ou plusieurs colonnes ont des caractères non utf_8";
    }
?>
    <head>
        <meta charset="utf-8" />
        <link rel="stylesheet" href="style/modifaddsupp.css" />
        <link rel="shortcut icon" href="#" />
        <title>AJOUTER <?php echo $table ;?></title>
    </head>

<body>
    <h1>Vous ajoutez des données dans la table <?php echo $table ;?></h1>

<!---------------------------Début du tableau---------------------------------------->
<form action="" method="GET">
<table>
    <?php
        //Parcourt toutes les colonnes de la table
        foreach($colonnes as $colonne)
        {
            $colonne = $colonne['Field'];
            //Récupération des filtres et leurs valeurs
            if(isset($_GET['filtre_'.$colonne]))
            {
                $nom_filtre = "filtre_$colonne";
                $value_filtre = $_GET['filtre_'.$colonne];
            }
            if($table !="callsignsroutes")
            {
                ?>
                <tr>
                    <td id="colonne"><?php echo $colonne;?></td>
                <?php
            }
            //----------------------SI AIRCRAFTS OU CALLSIGNSROUTES----------------------
            if($table=="aircrafts" || $table=="callsignsroutes")
            {
                //-----Ouverture du fichier des dépendances------
                //Nom du fichier
                $filename = "specific_rules";
                //Ouverture du fichier en mode lecture
                $fp = fopen($filename, "r");
                //Parcourt les lignes une à une du fichier
                while (($line = fgets($fp)) !== false)
                {
                    //Récupère le nom de la colonne
                    $search_colonne = strtok($line, '=');
                    //Si le nom de la colonne correspond
                    if($search_colonne == $colonne)
                    {
                        //Permet de chercher si il y a des dependances liés à cette colonne
                        $valide_aircrafts = strpos($line,",");
                        $valide_callsignsroutes = strpos($line,"display");
                        //Si il y a une virgule dans la ligne alors valide est oui
                        if(gettype($valide_aircrafts) =="integer"){$valide_aircrafts="oui";}
                        if(gettype($valide_callsignsroutes) =="integer"){$valide_callsignsroutes="oui";}
                        //Récupère la table dans laquelle aller chercher les informations
                        $find_table = strtok($line,",");//Prend ce qu'il y a avant la virgule
                        $find_table = str_replace("$colonne=table:","",$find_table);//Retire le debut et donc ne garde que la table
                        //Récupère la colonne de la table dans laquelle aller chercher les informations
                        $find_colonne = substr(str_replace("$colonne=table:$find_table,colonne:","",$line),0,-1);
                        if($table =="aircrafts")//Si la table est aircrafts
                        {
                            if($colonne =="updated")//Si la colonne est updated et table =aircrafts
                            {
                                $updated = date("Y-m-d H:i:s");//Récupère la date à l'instant t 
                                ?>
                                <td><?php echo $updated; ?></td>
                                <?php
                            }
                            if($colonne =="majuser")//Si la colonne est majuser et table = aircrafts
                            {
                                ?>
                                <td><?php echo $user ?></td>
                                <?php
                            }
                            if($colonne !="majuser" &&  $colonne!="updated")//Autres colonnes
                            {
                                //Value essaye de récupérer la valeur dans l'URL si valeur GET n'existe pas alors value =""
                                ?>
                                <td><input id ="<?php echo $colonne ;?>"name="<?php echo $colonne;?>"type="text" value="<?php echo $_GET[$colonne] ?? "" ;?>">
                                <?php
                                //Si il y a une correspondance dans une autre table
                                if($valide_aircrafts =="oui")
                                {
                                    //Instanciation d'une liste avec appel de la fonction JS
                                    ?>
                                    <select id="<?php echo "liste.$colonne" ;?>" onchange="change(this)">
                                    <?php
                                    $liste = liste_deroulante($db,$find_table,$find_colonne);//Requête SQL pour récupérer les élements à mettre dans la liste
                                    foreach($liste as $element)//Itére sur chaque élement de la liste
                                    {
                                        $element = $element['colonne'];//Récupère l'élement
                                        if($_GET[$colonne] == $element)//Si la valeur transmise auparavant est égale à l'élément
                                        {
                                            //L'option dans la liste sera choisi par défaut
                                            ?>
                                            <option value="<?php echo $element ;?>" selected><?php echo $element ;?></option>
                                            <?php
                                        }
                                        else
                                        {
                                            //Aucune option ne sera choisi par défaut, donc la 1ère sera présélectionné
                                            ?>
                                            <option value="<?php echo $element ;?>"><?php echo $element ;?></option>
                                            <?php
                                        }
                                    }
                                    //Fermeture de la liste
                                    ?>
                                    </select></td>
                                    <?php
                                }
                            }//FIN : autres colonnes
                        }//FIN : Table Aircrafts
                        //Si la table = callsignroutes et que le champ est dans les dépendances
                        if($table =="callsignsroutes")
                        {
                            if($valide_callsignsroutes =="oui")
                            {
                                //Afficher que les colonnes nécessaires à remplir les autres automatiquement
                                ?>
                                <tr>
                                    <td id="colonne"><?php echo $colonne;?></td>
                                    <td><input id="<?php echo $colonne ;?>" name="<?php echo $colonne ;?>" type="text" value="<?php echo $_GET[$colonne] ?? "";?>" onkeyup="toggleFile(this);">
                                <?php
                            }
                            else//Permet de ne générer aucune erreur en transmettant les valeurs dans l'URL
                            {
                                ?>
                                <input id="<?php echo $colonne ;?>" name="<?php echo $colonne ;?>" type="hidden" value="">
                                <?php
                            }
                        }
                    }//FIN : Si le nom de la colonne correspond 
                }//FIN : Parcourt les lignes une à une du fichier
            }//FIN : SI AIRCRAFTS OU CALLSIGNSROUTES
            else //Toutes les autres tables
            {
                //Itère sur chaque colonne une à une
                ?>
                <td><input name="<?php echo $colonne;?>"type="text" value="<?php echo $_GET[$colonne] ?? "";?>"></td>
                <?php
            }
            ?>
            </tr>
            <?php
        }//FIN : Parcourt toutes les colonnes de la table

?>
</table>


<div id="bouton">
    <form action="" method="GET">
    <input type="submit" name="add" value="Ajouter">
    <!---Input de type Hidden de toutes les données préalablement transmises------->
        <input type="hidden" name="table" value="<?php echo $table;?>">
        <input type="hidden" name="primary_key" value="<?php echo $primary_key;?>">
        <input type="hidden" name="primary_value" value="<?php echo $primary_value;?>">
        <input type="hidden" name="nb_debut_ligne" value="<?php echo $nb_debut_ligne;?>">
        <input type="hidden" name="nb_lignes" value="<?php echo $nb_lignes;?>">
        <input type="hidden" name="showtri" value="<?php echo $showtri ;?>">
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
    <form action="majtab.php" method="GET">
    <!---Input de type Hidden de toutes les données préalablement transmises------->
        <input type="submit" name="quitter" value="Retour">
        <input type="hidden" name="table" value="<?php echo $table;?>">
        <input type="hidden" name="primary_key" value="<?php echo $primary_key;?>">
        <input type="hidden" name="primary_value" value="<?php echo $primary_value;?>">
        <input type="hidden" name="nb_debut_ligne" value="<?php echo $nb_debut_ligne;?>">
        <input type="hidden" name="nb_lignes" value="<?php echo $nb_lignes;?>">
        <input type="hidden" name="showtri" value="<?php echo $showtri ;?>">
        <input type="hidden" name="<?php echo $nom_filtre;?>" value="<?php echo $value_filtre;?>">
    </form>
</div>

<?php
    //Quand l'utilisateur clique sur le bouton ajouter
    if(isset($_GET['add']))
    {
        //Instanciation de la chaine a prendre en compte dans la requete
        $add = "";
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
            $list_get = array("callsign","fromairporticao","fromairportiata","toairporticao","toairportiata");
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
            $add = ajouter($db,$_GET['callsign'],$_GET['fromairporticao'] ??"",$_GET['toairporticao']??"",$_GET['fromairportiata']??"",$_GET['toairportiata']??"");
        }
        else
        {
            foreach($colonnes as $colonne)
            {
                $colonne = $colonne['Field'];
                $value = $_GET[$colonne];
                //Enlève les guillemets
                $list = array('"',"'");
                $value = str_ireplace($list,"",$value);
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
                $add .= "'".$value."',";
            }
        }
        //Enlève la dernière virgule de la chaine
        $add = rtrim($add, ", ");
        $non_utf8 = rtrim($non_utf8, ", ");
        $non_type = rtrim($non_type,", ");
        $show_requete = "<b>INSERT INTO</b> $table <b>VALUES</b> ($add)";

        if($utf_8 == true && $bool_type == true)
        {
            ?>
            <!----Affichage du bouton pour valider la requête ou non------->
            <div id="bouton"><p> Êtes vous sûrs de vouloir exécuter cette requête ?</p>
                <p><?php echo $show_requete ?></p>
                <form action="" method="GET">
                <!---------------- Récupération de toutes les variables----------------------->
                <input type="submit" name="valider" value="Oui">
                <input type="submit" name="valider" value="Non">
                <input type="hidden" name="table" value="<?php echo $table ;?>">
                <input type="hidden" name="primary_key" value="<?php echo $primary_key;?>">
                <input type="hidden" name="primary_value" value="<?php echo $primary_value;?>">
                <input type="hidden" name="add" value="<?php echo $add ;?>">
                <input type="hidden" name="utf_8" value="<?php echo $utf_8 ;?>">
                <input type="hidden" name="nb_debut_ligne" value="<?php echo $nb_debut_ligne;?>">
                <input type="hidden" name="nb_lignes" value="<?php echo $nb_lignes;?>">
                <input type="hidden" name="showtri" value="<?php echo $showtri ;?>">
                <input type="hidden" name="<?php echo $nom_filtre;?>" value="<?php echo $value_filtre;?>">
                <?php
                //Renvoi des input hidden des valeurs rentrés précédemment
                foreach($colonnes as $colonne)
                {
                //Itère sur chaque colonne une à une
                ?>
                <input name="<?php echo $colonne['Field'];?>" type="hidden" value="<?php echo $_GET[$colonne['Field']];?>">
                <?php
                }
        }
        ?>
            </form>
        </div>

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
?>
    <!--Appel du code javascript--> 
    <script src="script/main.js"></script>

</body>

</html>