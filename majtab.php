<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8" />
        <link rel="stylesheet" href="style/main.css" />
        <title>ADS B BDD</title>
    </head>
<?php
    //Début de la session
    session_start();
    if($_SESSION['username'] !== ""){
        $user = $_SESSION['username'];
        echo "<p id='connect'>Connecté en tant que : $user</p>";
    }
    if(empty(isset($_SESSION['username'])))
    {
        echo " oeoeoeoe";
        header("location:login.php");
    }
    ?>      
        <p id="connect"><a href='login.php?deconnexion=true'>Se déconnecter</a></p>
        
        <!-- tester si l'utilisateur est connecté -->
        <?php
            if(isset($_GET['deconnexion']))
            { 
                if($_GET['deconnexion']==true)
                {  
                    session_unset();
                    header("location:login.php");
                }
            }
    //------------------Appel des fonctions--------------------
    require "module/function.php";
    //Connexion à la BDD
    $db = connect_request();
    //Voir toutes les tables de la BDD
    $tables = all_tables($db);

    ?>
    <body>
    <!----------Choix de la table et du nombre de lignes----------->
    <form action="" method="GET">
        <!------Liste déroulante des tables dans la BDD----------------------->
        <p>
        <label>Table</label>
        <select name="table">
            <?php
            //Parcourir le tableau des stations
            //Nouveau compteur pour mettre le numéro de la station
            $cpt = 0;
            foreach($tables as $showtable){
                //Incrémentation du compteur
                $cpt++;
                //Permet de garder en sélection la table choisie
                if($_GET['table'] == $showtable['Tables_in_test'])
                {
                    ?>
                    <option selected="true" value="<?php echo strtolower($showtable['Tables_in_test']); ?>"><?php echo $cpt.".".$showtable['Tables_in_test'];?></option>
                    <?php
                }else{
                    ?>
                    <option value="<?php echo strtolower($showtable['Tables_in_test']); ?>"><?php echo $cpt.".".$showtable['Tables_in_test'];?></option>
                <?php
                }
            }
            ?>
        </select>
        </p>
        <!--------------Liste déroulante du nombre de lignes à afficher-------------->
        <p>
        <label>Nombre de lignes à afficher</label>
        <select name="nb_lignes">
            <?php
            $cpt = 0;
            while($cpt <100){
                $cpt += 10;
                //Permet de garder en sélection le nombre de lignes choisies
                if($_GET['nb_lignes'] == $cpt){
                    ?>
                    <option selected="true" value="<?php echo strtolower($cpt);?>"><?php echo $cpt;?></option>
                    <?php
                }else{
                    ?>
                    <option value="<?php echo strtolower($cpt);?>"><?php echo $cpt;?></option>
                <?php
                }
            }
            ?>
        </select>
        </p>
        <!-- Instanciation du nb de debut lignes -->
        <input type="hidden" name="nb_debut_ligne" value ="0">
        <!---Instanciation du tri -->
        <input type="hidden" name="showtri" value="croissant">
        <input type="hidden" name="filtre" value="">
        <!-- Bouton permettant de transmettre les valeurs selectionnés-->
        <input type="submit" name="voirtable" value="Voir la table">
    </form>
    
    <!--------------Gestion des valeurs sélectionnées-------------->
    <?php
//-----------PERMET DE GENERER AUCUNE ERREUR LORSQU'ON ARRIVE SUR LA PAGE----------------------
//Si l'utilisateur clique sur le bouton pour voir une table
    if(isset($_GET['voirtable']) || isset($_GET['page']) || isset($_GET['quitter']) || isset($_GET['filtre']) || isset($_GET['showtri']))
    {
        //La variable table prend cette valeur (Table à afficher)
        $table = $_GET['table'];
        //La variable nb_lignes prend cette valeur (nombre de lignes à afficher)
        $nb_lignes = $_GET['nb_lignes'];
        //La variable nb_debut_ligne prend cette valeur (nombre de départ)
        $nb_debut_ligne = $_GET['nb_debut_ligne'];
        //Variable contenant les colonnes
        $colonnes = colonne_table($table,$db);
        //Compteur de lignes à afficher dans le tableau
        $cpt_ligne = $_GET['nb_debut_ligne']+1;
        //Récupération des valeurs de tri
        $showtri=$_GET['showtri'];
        if($showtri =="decroissant"){$tri = "DESC";}
        if($showtri =="croissant"){$tri = "ASC";}
        //Récupération de la clé primaire
        $primary_key = primary($db,$table);
        $tricolonne = $primary_key;
        if(empty($tricolonne))
        {
            $tricolonne = "Pas de colonne";
        }
        //Permet de générer aucune erreur si pas de filtres
        foreach($colonnes as $colonne)
        {
            $nom_filtre = isset($_GET[$colonne['Field']]) ? $_GET[$colonne['Field']] : NULL;
            $value_filtre = isset($_GET['filtre_'.$colonne['Field']]) ? $_GET['filtre_'.$colonne['Field']] : NULL;
        }
        //----------------------------Si un filtre est appliqué-------------------------------------------
        foreach($colonnes as $colonne)
        {
            if(isset($_GET['filtre']))
            {
                //Réinitialisation du debut de ligne à 0
                $nb_debut_ligne = 0;
            }
            if(isset($_GET['filtre_'.$colonne['Field']]))
            {
                $nom_filtre = $colonne['Field'];
                $value_filtre = $_GET['filtre_'.$colonne['Field']];
                $tricolonne = $nom_filtre;
            }
        }
        //Variable contenant les tuples à afficher
        $tuples = data_table($table,$db,$nb_debut_ligne,$nb_lignes,$nom_filtre,$value_filtre,$primary_key,$tri);
        //Nombre de lignes dans la table à afficher
        $nb_total = nb_total($db,$table,$nom_filtre,$value_filtre);

        //--Appel des fonctions page précédente / suivante sur clique bouton-->
        $page = isset($_GET['page']) ? $_GET['page'] : NULL; //Récupère la valeur des boutons

        if($page=="Précédent")
        {
            $nb_debut_ligne = PagePrecedente($nb_debut_ligne,$nb_lignes);
            //Instanciation des variables contenant des colonnes et des tuple
            $colonnes = colonne_table($table,$db);
            $tuples = data_table($table,$db,$nb_debut_ligne,$nb_lignes,$nom_filtre,$value_filtre,$primary_key,$tri);
            //Nombre de lignes dans la table
            $nb_total = nb_total($db,$table,$nom_filtre,$value_filtre);
        }

        if($page=="Suivant")
        { 
            $nb_debut_ligne = PageSuivante($nb_debut_ligne,$nb_lignes,$nb_total);
            //Instanciation des variables contenant des colonnes et des tuple
            $colonnes = colonne_table($table,$db);
            $tuples = data_table($table,$db,$nb_debut_ligne,$nb_lignes,$nom_filtre,$value_filtre,$primary_key,$tri);
            //Nombre de lignes dans la table
            $nb_total = nb_total($db,$table,$nom_filtre,$value_filtre);
        }

        ?>

        <h1><?php echo $table ?></h1>
        <!-----------Obligation de bouclé sur le résultat d'une requête SQL--------------------------->
        <p>Affichage des lignes <?php echo $nb_debut_ligne+1;?> - <?php echo $nb_debut_ligne+$nb_lignes ;?> (total de : <?php foreach($nb_total as $nb){ echo $nb['nb_total'];} ;?>)</p>
        <!--Choix du type de tri : Croissant / Décroissant-->
        <p>Ordre de tri</p>
        <form action="" method)="GET">
            <div>
            <input type="hidden" name="table" value="<?php echo $table ;?>">
            <input type="hidden" name="nb_lignes" value="<?php echo $nb_lignes ;?>">
            <input type="hidden" name="nb_debut_ligne" value="<?php echo $nb_debut_ligne ;?>">
            <?php
            //Permet de transmettre les filtres si il y en a
            foreach($colonnes as $colonne)
            {
                $colonne = $colonne['Field'];
                if(isset($_GET['filtre_'.$colonne]))
                {
                ?>
            <input type="hidden" name="<?php echo 'filtre_'.$colonne;?>" value="<?php echo $_GET['filtre_'.$colonne];?>">
            <?php
                }
            }
            ?>
            <input type="submit" id="croissant" name="showtri" value="croissant">
            <input type="submit" id="decroissant" name="showtri" value="decroissant">
            </div>
        </form> 
        <p>Colonne : <?php echo $tricolonne;?> | Tri : <?php echo $_GET['showtri'];?></p>
        <!----------Compteur de lignes à afficher dans le tableau----------->
        <?php $cpt_ligne = $nb_debut_ligne+1;?>
<!----------Début du tableau----------->
        <table>
            <!--Nom de colonne du tableau-->
            <thead>
            <?php
            //Variable comptant le nombre de colonnes
            $nb_colonne=-1;
            ?>
            <!------Colonne pour afficher le numéro des lignes MODIFIER / SUPPRIMER / NUM DE LIGNES----->
            <!--MODIFIER-->
            <th></th>
            <!--SUPPRIMER-->
            <th></th>
            <!--NUMERO DE LIGNES-->
            <th></th>
            <?php 
            //Colonne pour afficher le numéro des lignes
            foreach($colonnes as $colonne)
            {
                ?>
                <th>
                    <!------------------------------FILTRES------------------------------------------->
                    <form action="" method="GET">
                    <p><input type="submit" name="filtre" value="Appliquer filtres"></p>
                    <?php
                        //Permet d'afficher le filtre appliqué
                        if(isset($_GET['filtre_'.$colonne['Field']]))
                        {
                            ?>
                            <input name="<?php echo 'filtre_'.$colonne['Field'];?>"type="text" value="<?php echo $_GET['filtre_'.$colonne['Field']];?>">
                            <?php
                        }
                        else
                        {
                            ?>
                            <input name="<?php echo 'filtre_'.$colonne['Field'];?>"type="text" >
                            <?php
                        }
                        ?>
                        <input type="hidden" name="table" value="<?php echo $table ;?>">
                        <input type="hidden" name="nb_lignes" value="<?php echo $nb_lignes ;?>">
                        <input type="hidden" name="nb_debut_ligne" value="<?php echo $nb_debut_ligne ;?>">
                        <input type="hidden" name="showtri" value="<?php echo $showtri;?>">

                    </form>
                    
                    <!--------------Nom de la colonne----------------->
                    <?php
                    //Met une couleur différente à la colonne qui a un filtre
                    if(isset($_GET['filtre_'.$colonne['Field']]))
                    {
                        ?>
                        <p id="filtre"><?php echo $colonne['Field'];?></p>
                        <?php
                    }
                    else
                    {
                        //Met une couleur différente à la colonne qui est la clé primaire
                        if($primary_key == $colonne['Field'] && $nom_filtre=="")
                        {
                            ?>
                            <p id="filtre"><?php echo $colonne['Field'];?></p>
                            <?php
                        }
                        //Affiche la colonne de base
                        else
                        {
                        ?>
                        <p><?php echo $colonne['Field'];?></p>
                        <?php
                        }
                    }
                    ?>
                </th>
                
                <?php
                //Récupère la clé primaire de la table
                if($colonne['Key'] =="PRI"){
                    $primary_key = $colonne['Field'];
                    $primary_cpt = $nb_colonne+1;
                }
                $nb_colonne++;
            }
            //Si il n'y a pas de clé primaire dans la table
            if(empty($primary_key))
            {
                $primary_key ="";
                $primary_cpt="";
                $primary_value="";
            }
            ?>
            </thead>
            <tbody>
    

            <?php
            //Boucle itérant sur chaque ligne de la table
            foreach($tuples as $tuple)
            {
                //Compteur permettant d'afficher chaque colonne une à une
                $cpt=0;
                ?>
                <!--Cellule permettant le changement de ligne-->
                <tr>
                    <?php
                    //Valeur de la clé primaire prend sa valeur
                    if($primary_key != "")
                    {
                        $primary_value = $tuple[$primary_cpt];
                    }
                    ?>
                    <!-----Numéro de la ligne----->
                    <?php
                    //---------------------------LIEN MODIFIER / SUPPRIMER --------------------------------------------
                    //Permet de transmettre les filtres si il y en a
                    foreach($colonnes as $colonne)
                    {
                        if(isset($_GET['filtre_'.$colonne['Field']]))
                        {
                            $nom_filtre = $colonne['Field'];
                            $value_filtre = $_GET['filtre_'.$colonne['Field']];
                        }
                    }
                    if(!isset($nom_filtre))
                    {
                        $filtre="";
                        $nom_filtre="";
                    }
                    ?>
                    <td><a <?php echo "href='modifier.php/?table=$table&primary_key=$primary_key&primary_value=$primary_value&nb_debut_ligne=$nb_debut_ligne&nb_lignes=$nb_lignes&showtri=$showtri&filtre_$nom_filtre=$value_filtre'"?>>M</td>
                    <td><a <?php echo "href='supprimer.php/?table=$table&primary_key=$primary_key&primary_value=$primary_value&nb_debut_ligne=$nb_debut_ligne&nb_lignes=$nb_lignes&showtri=$showtri&filtre_$nom_filtre=$value_filtre'"?>>S</td>
                    <td><?php echo $cpt_ligne;?></td>
                    <?php
                    $cpt_ligne++;
                    //Itère sur chaque colonne une à une
                    while($cpt <= $nb_colonne):
                        ?>
                        <td><?php echo $tuple[$cpt]; ?></td>
                        <?php
                        $cpt++;
                    endwhile;
                ?>
            </tr>
            <?php
            }
            ?>
<!-------------Fin du tableau------------->
            </tbody>
        </table>
<!----------Précedent / Suivant------------>
        <div id="bouton">
        <form action="" method="GET">
            <input type="hidden" name="table" value="<?php echo $table ;?>">
            <input type="hidden" name="nb_lignes" value="<?php echo $nb_lignes ;?>">
            <input type="hidden" name="nb_debut_ligne" value="<?php echo $nb_debut_ligne ;?>">
            <input type="hidden" name="showtri" value="<?php echo $showtri ;?>">
            <?php
            //Permet de transmettre les filtres si il y en a
            foreach($colonnes as $colonne)
            {
                if(isset($_GET['filtre_'.$colonne['Field']]))
                {
                ?>
                <input type="hidden" name="<?php echo 'filtre_'.$colonne['Field'];?>" value="<?php echo $_GET['filtre_'.$colonne['Field']];?>">
                <?php
                }
            }
            ?>
            <input id ="precedent" type="submit" name="page" value="Précédent">
            <input id="suivant" type="submit" name="page" value="Suivant">
        </form>
        </div>

<!--------------Ajouter une ligne-------------->
        <form action="ajouter.php" method="GET">
        <input type="submit" name="ajouter" value="Ajouter">
        <input type="hidden" name="table" value="<?php echo $table ;?>">
        <input type="hidden" name="nb_debut_ligne" value="<?php echo $nb_debut_ligne ;?>">
        <input type="hidden" name="nb_lignes" value="<?php echo $nb_lignes ;?>">
        <input type="hidden" name="showtri" value="<?php echo $showtri ;?>">
        <?php
        //Permet de transmettre les filtres si il y en a
        foreach($colonnes as $colonne)
        {
            if(isset($_GET['filtre_'.$colonne['Field']]))
            {
            ?>
        <input type="hidden" name="<?php echo 'filtre_'.$colonne['Field'];?>" value="<?php echo $_GET['filtre_'.$colonne['Field']];?>">
        <?php
            }
        }
        ?>
        </form>
        <!--Appel du code javascript--> 
        <!--<script src="script/main.js"></script>-->
<!-----FIN DE LA CONDITION QUI PERMET DE GENERER AUCUNE ERREUR--------->
</body>
<?php }?>
</html>