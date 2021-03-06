<?php

function get_config()
{
    //Récupération des variables dans le fichier de config
    $connect = file('.config.cfg');
    foreach($connect as $line)
    {
        $pos = strpos($line, '=');
        $conf[trim(substr($line,0,$pos))] = trim(substr($line, $pos+1));
    }
    return $conf;
}
//---------------------Connexion à la BDD---------------
function connect_request()//Pour les requetes
{
    try
    {
        //Appel de la fonction pour récupérer le dictionnaire
        $conf = get_config();
        //Récupération des variables
        $host=$conf['host_request'];
        $port=$conf['port_request'];
        $database=$conf['db_request'];
        $user=$conf['user_request'];
        $pwd=$conf['pwd_request'];
        $db = new PDO("mysql:host=$host;port=$port;dbname=$database;charset=utf8", "$user", "$pwd",[PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION],);
    }
    catch (Exception $e)
    {
            die('Erreur : ' . $e->getMessage());
    }
    return $db;
}
function connect_user()//Pour se connecter
{
    try
    {
        //Appel de la fonction pour récupérer le dictionnaire
        $conf = get_config();
        //Récupération des variables
        $host=$conf['host_user'];
        $port=$conf['port_user'];
        $database=$conf['db_user'];
        $user=$conf['user_user'];
        $pwd=$conf['pwd_user'];
        $db = new PDO("mysql:host=$host;port=$port;dbname=$database;charset=utf8", "$user", "$pwd",[PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION],);
    }
    catch (Exception $e)
    {
            die('Erreur : ' . $e->getMessage());
    }
    return $db;
}
// //Début de la session

//--------------------------MAJTAB----------------------------
//Récupérer la clé primaire de la table
function primary($db,$table)
{
    $primaryStatement = $db->prepare("SHOW KEYS FROM $table WHERE Key_name = 'PRIMARY' ");
    $primaryStatement->execute();
    $primary_key = $primaryStatement->fetchAll();
    foreach($primary_key as $pri)
    {
        $primary_key = $pri['Column_name'];
    }
    return $primary_key;
}
//Nombre de tuples dans la table
function nb_total($db,$table,$nom_filtre,$value_filtre)
{
    //Si un filtre est appliqué
    if($nom_filtre!="")
    {
        $nbtotalStatement = $db->prepare("SELECT COUNT(*) as nb_total FROM $table WHERE $nom_filtre LIKE '$value_filtre%'");
        $nbtotalStatement->execute();
        $nb_total = $nbtotalStatement->fetchAll();
    }
    else{
        $nbtotalStatement = $db->prepare('SELECT COUNT(*) as nb_total FROM '.$table.';');
        $nbtotalStatement->execute();
        $nb_total = $nbtotalStatement->fetchAll();
    }
    return $nb_total;
}
//Passe à la page suivante
function PageSuivante($nb_debut_ligne,$nb_lignes,$nb_total)
{ 
    //Nombre de début + le nombre de fin
    $nb_debut_ligne += $nb_lignes;
    foreach($nb_total as $nb){
        $nb_total =  $nb['nb_total'];}
    //-----------Gestion des erreurs--------------
    //nb_debut_ligne ne peut être supérieur à nb_total
    if($nb_debut_ligne >= $nb_total){
        $nb_debut_ligne -= $nb_lignes;
    }
    return $nb_debut_ligne;
}
//Reviens à la page précédente
function PagePrecedente($nb_debut_ligne,$nb_lignes)
{
    //Nombre de début - le nombre de fin
    $nb_debut_ligne -= $nb_lignes;
    //-----------Gestion des erreurs--------------
    //nb_debut_ligne ne peut pas être négatif
    if($nb_debut_ligne <=0) {
        $nb_debut_ligne = 0;
    }
    return $nb_debut_ligne;
}

//Voir toutes les tables de la BDD
function all_tables($db)
{
    $showtableStatement = $db->prepare('SHOW TABLES');
    $showtableStatement->execute();
    $tables = $showtableStatement->fetchAll();
    return $tables;
}
//Affiche les colonnes de la table séléctionné
function colonne_table($table,$db)
{
    //Récupérer le nom des colonnes
    $requete = $db->prepare("DESCRIBE $table");
    $requete->execute();
    $colonnes = $requete->fetchAll();
    return $colonnes;
}
//Affiche les tuples de la table sélectionné
function data_table($table,$db,$nb_debut_ligne,$nb_ligne,$nom_filtre,$value_filtre,$primary_key,$tri)
{
    //Si un filtre est appliqué
    if($nom_filtre!="")
    {
        $requete = $db->prepare("SELECT * FROM $table WHERE $nom_filtre LIKE '$value_filtre%' ORDER BY $nom_filtre $tri LIMIT $nb_debut_ligne,$nb_ligne");
        $requete->execute();
        $tuples = $requete->fetchAll();
    }
    //Si aucun filtre n'est appliqué
    if($nom_filtre=="")
    {
        //Si aucun filtre et aucune clé primaire
        if(empty($primary_key))
        {
            $requete = $db->prepare("SELECT * FROM $table LIMIT $nb_debut_ligne,$nb_ligne");
            $requete->execute();
            $tuples = $requete->fetchAll();
        }
        //Si aucun filtre et clé primaire
        else
        {
        $requete = $db->prepare("SELECT * FROM $table ORDER BY $primary_key $tri LIMIT $nb_debut_ligne,$nb_ligne");
        $requete->execute();
        $tuples = $requete->fetchAll();
        }
    }
    return $tuples;
}
//-------------------------------MODIFIER----------------------------------------------
function tuples($db,$table,$primary_key,$primary_value){
    //Récupère les tuples
    $requete = $db->prepare("SELECT * FROM $table WHERE $primary_key = '$primary_value'");
    $requete->execute();
    $tuples = $requete->fetchAll();
    return $tuples;
}

//-------------------------------Nettoyage des données-------------------------------------------
//Décodage utf_8
function test_utf8($str)
{
  if (is_array($str)) {
     $str = implode('', $str);
     // retourne FALSE si aucun caractère n'appartient au jeu utf8
     return !((ord($str[0]) != 239) && (ord($str[1]) != 187) && (ord($str[2]) != 191));
    }
    else {
        // retourne TRUE
        // si la chaine décoder et encoder est égale à elle même
        return (utf8_encode(utf8_decode($str)) == $str);
    }
}
//Enlève les accents
function unaccent($str)
{
  $transliteration = array(
    'Ĳ' => 'I', 'Ö' => 'O','Œ' => 'O','Ü' => 'U','ä' => 'a','æ' => 'a',
    'ĳ' => 'i','ö' => 'o','œ' => 'o','ü' => 'u','ß' => 's','ſ' => 's',
    'À' => 'A','Á' => 'A','Â' => 'A','Ã' => 'A','Ä' => 'A','Å' => 'A',
    'Æ' => 'A','Ā' => 'A','Ą' => 'A','Ă' => 'A','Ç' => 'C','Ć' => 'C',
    'Č' => 'C','Ĉ' => 'C','Ċ' => 'C','Ď' => 'D','Đ' => 'D','È' => 'E',
    'É' => 'E','Ê' => 'E','Ë' => 'E','Ē' => 'E','Ę' => 'E','Ě' => 'E',
    'Ĕ' => 'E','Ė' => 'E','Ĝ' => 'G','Ğ' => 'G','Ġ' => 'G','Ģ' => 'G',
    'Ĥ' => 'H','Ħ' => 'H','Ì' => 'I','Í' => 'I','Î' => 'I','Ï' => 'I',
    'Ī' => 'I','Ĩ' => 'I','Ĭ' => 'I','Į' => 'I','İ' => 'I','Ĵ' => 'J',
    'Ķ' => 'K','Ľ' => 'K','Ĺ' => 'K','Ļ' => 'K','Ŀ' => 'K','Ł' => 'L',
    'Ñ' => 'N','Ń' => 'N','Ň' => 'N','Ņ' => 'N','Ŋ' => 'N','Ò' => 'O',
    'Ó' => 'O','Ô' => 'O','Õ' => 'O','Ø' => 'O','Ō' => 'O','Ő' => 'O',
    'Ŏ' => 'O','Ŕ' => 'R','Ř' => 'R','Ŗ' => 'R','Ś' => 'S','Ş' => 'S',
    'Ŝ' => 'S','Ș' => 'S','Š' => 'S','Ť' => 'T','Ţ' => 'T','Ŧ' => 'T',
    'Ț' => 'T','Ù' => 'U','Ú' => 'U','Û' => 'U','Ū' => 'U','Ů' => 'U',
    'Ű' => 'U','Ŭ' => 'U','Ũ' => 'U','Ų' => 'U','Ŵ' => 'W','Ŷ' => 'Y',
    'Ÿ' => 'Y','Ý' => 'Y','Ź' => 'Z','Ż' => 'Z','Ž' => 'Z','à' => 'a',
    'á' => 'a','â' => 'a','ã' => 'a','ā' => 'a','ą' => 'a','ă' => 'a',
    'å' => 'a','ç' => 'c','ć' => 'c','č' => 'c','ĉ' => 'c','ċ' => 'c',
    'ď' => 'd','đ' => 'd','è' => 'e','é' => 'e','ê' => 'e','ë' => 'e',
    'ē' => 'e','ę' => 'e','ě' => 'e','ĕ' => 'e','ė' => 'e','ƒ' => 'f',
    'ĝ' => 'g','ğ' => 'g','ġ' => 'g','ģ' => 'g','ĥ' => 'h','ħ' => 'h',
    'ì' => 'i','í' => 'i','î' => 'i','ï' => 'i','ī' => 'i','ĩ' => 'i',
    'ĭ' => 'i','į' => 'i','ı' => 'i','ĵ' => 'j','ķ' => 'k','ĸ' => 'k',
    'ł' => 'l','ľ' => 'l','ĺ' => 'l','ļ' => 'l','ŀ' => 'l','ñ' => 'n',
    'ń' => 'n','ň' => 'n','ņ' => 'n','ŉ' => 'n','ŋ' => 'n','ò' => 'o',
    'ó' => 'o','ô' => 'o','õ' => 'o','ø' => 'o','ō' => 'o','ő' => 'o',
    'ŏ' => 'o','ŕ' => 'r','ř' => 'r','ŗ' => 'r','ś' => 's','š' => 's',
    'ť' => 't','ù' => 'u','ú' => 'u','û' => 'u','ū' => 'u','ů' => 'u',
    'ű' => 'u','ŭ' => 'u','ũ' => 'u','ų' => 'u','ŵ' => 'w','ÿ' => 'y',
    'ý' => 'y','ŷ' => 'y','ż' => 'z','ź' => 'z','ž' => 'z','Α' => 'A',
    'Ά' => 'A','Ἀ' => 'A','Ἁ' => 'A','Ἂ' => 'A','Ἃ' => 'A','Ἄ' => 'A',
    'Ἅ' => 'A','Ἆ' => 'A','Ἇ' => 'A','ᾈ' => 'A','ᾉ' => 'A','ᾊ' => 'A',
    'ᾋ' => 'A','ᾌ' => 'A','ᾍ' => 'A','ᾎ' => 'A','ᾏ' => 'A','Ᾰ' => 'A',
    'Ᾱ' => 'A','Ὰ' => 'A','ᾼ' => 'A','Β' => 'B','Γ' => 'G','Δ' => 'D',
    'Ε' => 'E','Έ' => 'E','Ἐ' => 'E','Ἑ' => 'E','Ἒ' => 'E','Ἓ' => 'E',
    'Ἔ' => 'E','Ἕ' => 'E','Ὲ' => 'E','Ζ' => 'Z','Η' => 'I','Ή' => 'I',
    'Ἠ' => 'I','Ἡ' => 'I','Ἢ' => 'I','Ἣ' => 'I','Ἤ' => 'I','Ἥ' => 'I',
    'Ἦ' => 'I','Ἧ' => 'I','ᾘ' => 'I','ᾙ' => 'I','ᾚ' => 'I','ᾛ' => 'I',
    'ᾜ' => 'I','ᾝ' => 'I','ᾞ' => 'I','ᾟ' => 'I','Ὴ' => 'I','ῌ' => 'I',
    'Θ' => 'T','Ι' => 'I','Ί' => 'I','Ϊ' => 'I','Ἰ' => 'I','Ἱ' => 'I',
    'Ἲ' => 'I','Ἳ' => 'I','Ἴ' => 'I','Ἵ' => 'I','Ἶ' => 'I','Ἷ' => 'I',
    'Ῐ' => 'I','Ῑ' => 'I','Ὶ' => 'I','Κ' => 'K','Λ' => 'L','Μ' => 'M',
    'Ν' => 'N','Ξ' => 'K','Ο' => 'O','Ό' => 'O','Ὀ' => 'O','Ὁ' => 'O',
    'Ὂ' => 'O','Ὃ' => 'O','Ὄ' => 'O','Ὅ' => 'O','Ὸ' => 'O','Π' => 'P',
    'Ρ' => 'R','Ῥ' => 'R','Σ' => 'S','Τ' => 'T','Υ' => 'Y','Ύ' => 'Y',
    'Ϋ' => 'Y','Ὑ' => 'Y','Ὓ' => 'Y','Ὕ' => 'Y','Ὗ' => 'Y','Ῠ' => 'Y',
    'Ῡ' => 'Y','Ὺ' => 'Y','Φ' => 'F','Χ' => 'X','Ψ' => 'P','Ω' => 'O',
    'Ώ' => 'O','Ὠ' => 'O','Ὡ' => 'O','Ὢ' => 'O','Ὣ' => 'O','Ὤ' => 'O',
    'Ὥ' => 'O','Ὦ' => 'O','Ὧ' => 'O','ᾨ' => 'O','ᾩ' => 'O','ᾪ' => 'O',
    'ᾫ' => 'O','ᾬ' => 'O','ᾭ' => 'O','ᾮ' => 'O','ᾯ' => 'O','Ὼ' => 'O',
    'ῼ' => 'O','α' => 'a','ά' => 'a','ἀ' => 'a','ἁ' => 'a','ἂ' => 'a',
    'ἃ' => 'a','ἄ' => 'a','ἅ' => 'a','ἆ' => 'a','ἇ' => 'a','ᾀ' => 'a',
    'ᾁ' => 'a','ᾂ' => 'a','ᾃ' => 'a','ᾄ' => 'a','ᾅ' => 'a','ᾆ' => 'a',
    'ᾇ' => 'a','ὰ' => 'a','ᾰ' => 'a','ᾱ' => 'a','ᾲ' => 'a','ᾳ' => 'a',
    'ᾴ' => 'a','ᾶ' => 'a','ᾷ' => 'a','β' => 'b','γ' => 'g','δ' => 'd',
    'ε' => 'e','έ' => 'e','ἐ' => 'e','ἑ' => 'e','ἒ' => 'e','ἓ' => 'e',
    'ἔ' => 'e','ἕ' => 'e','ὲ' => 'e','ζ' => 'z','η' => 'i','ή' => 'i',
    'ἠ' => 'i','ἡ' => 'i','ἢ' => 'i','ἣ' => 'i','ἤ' => 'i','ἥ' => 'i',
    'ἦ' => 'i','ἧ' => 'i','ᾐ' => 'i','ᾑ' => 'i','ᾒ' => 'i','ᾓ' => 'i',
    'ᾔ' => 'i','ᾕ' => 'i','ᾖ' => 'i','ᾗ' => 'i','ὴ' => 'i','ῂ' => 'i',
    'ῃ' => 'i','ῄ' => 'i','ῆ' => 'i','ῇ' => 'i','θ' => 't','ι' => 'i',
    'ί' => 'i','ϊ' => 'i','ΐ' => 'i','ἰ' => 'i','ἱ' => 'i','ἲ' => 'i',
    'ἳ' => 'i','ἴ' => 'i','ἵ' => 'i','ἶ' => 'i','ἷ' => 'i','ὶ' => 'i',
    'ῐ' => 'i','ῑ' => 'i','ῒ' => 'i','ῖ' => 'i','ῗ' => 'i','κ' => 'k',
    'λ' => 'l','μ' => 'm','ν' => 'n','ξ' => 'k','ο' => 'o','ό' => 'o',
    'ὀ' => 'o','ὁ' => 'o','ὂ' => 'o','ὃ' => 'o','ὄ' => 'o','ὅ' => 'o',
    'ὸ' => 'o','π' => 'p','ρ' => 'r','ῤ' => 'r','ῥ' => 'r','σ' => 's',
    'ς' => 's','τ' => 't','υ' => 'y','ύ' => 'y','ϋ' => 'y','ΰ' => 'y',
    'ὐ' => 'y','ὑ' => 'y','ὒ' => 'y','ὓ' => 'y','ὔ' => 'y','ὕ' => 'y',
    'ὖ' => 'y','ὗ' => 'y','ὺ' => 'y','ῠ' => 'y','ῡ' => 'y','ῢ' => 'y',
    'ῦ' => 'y','ῧ' => 'y','φ' => 'f','χ' => 'x','ψ' => 'p','ω' => 'o',
    'ώ' => 'o','ὠ' => 'o','ὡ' => 'o','ὢ' => 'o','ὣ' => 'o','ὤ' => 'o',
    'ὥ' => 'o','ὦ' => 'o','ὧ' => 'o','ᾠ' => 'o','ᾡ' => 'o','ᾢ' => 'o',
    'ᾣ' => 'o','ᾤ' => 'o','ᾥ' => 'o','ᾦ' => 'o','ᾧ' => 'o','ὼ' => 'o',
    'ῲ' => 'o','ῳ' => 'o','ῴ' => 'o','ῶ' => 'o','ῷ' => 'o','А' => 'A',
    'Б' => 'B','В' => 'V','Г' => 'G','Д' => 'D','Е' => 'E','Ё' => 'E',
    'Ж' => 'Z','З' => 'Z','И' => 'I','Й' => 'I','К' => 'K','Л' => 'L',
    'М' => 'M','Н' => 'N','О' => 'O','П' => 'P','Р' => 'R','С' => 'S',
    'Т' => 'T','У' => 'U','Ф' => 'F','Х' => 'K','Ц' => 'T','Ч' => 'C',
    'Ш' => 'S','Щ' => 'S','Ы' => 'Y','Э' => 'E','Ю' => 'Y','Я' => 'Y',
    'а' => 'A','б' => 'B','в' => 'V','г' => 'G','д' => 'D','е' => 'E',
    'ё' => 'E','ж' => 'Z','з' => 'Z','и' => 'I','й' => 'I','к' => 'K',
    'л' => 'L','м' => 'M','н' => 'N','о' => 'O','п' => 'P','р' => 'R',
    'с' => 'S','т' => 'T','у' => 'U','ф' => 'F','х' => 'K','ц' => 'T',
    'ч' => 'C','ш' => 'S','щ' => 'S','ы' => 'Y','э' => 'E','ю' => 'Y',
    'я' => 'Y','ð' => 'd','Ð' => 'D','þ' => 't','Þ' => 'T','ა' => 'a',
    'ბ' => 'b','გ' => 'g','დ' => 'd','ე' => 'e','ვ' => 'v','ზ' => 'z',
    'თ' => 't','ი' => 'i','კ' => 'k','ლ' => 'l','მ' => 'm','ნ' => 'n',
    'ო' => 'o','პ' => 'p','ჟ' => 'z','რ' => 'r','ს' => 's','ტ' => 't',
    'უ' => 'u','ფ' => 'p','ქ' => 'k','ღ' => 'g','ყ' => 'q','შ' => 's',
    'ჩ' => 'c','ც' => 't','ძ' => 'd','წ' => 't','ჭ' => 'c','ხ' => 'k',
    'ჯ' => 'j','ჰ' => 'h'
    );
    $str = str_replace(array_keys($transliteration),
                        array_values($transliteration),
                        $str);
    return $str;
}

function verif_type($colonnes,$table)
{
    //Vérifie si tous les champs ont des valeurs du bon type
    $bool_type = true;
    $cpt_non_type = 0;
    $non_type ="";
    //Pas besoin de vérification de type pour callsignsroutes
    if($table != "callsignsroutes")
    {
        foreach($colonnes as $colonne)
        {
            //Récupère le nom de la colonne
            $column = $colonne['Field'];
            //Récupère la valeur de la colonne, si en modification -> nul, alors récupère la clé primaire
            $value = $_GET[$column] ?? $_GET['primary_value'];
            //Récupère le type de la colonne
            $type = $colonne['Type'];
            //Si le type du champ est un int
            if(strpos($type,"int")=="true")
            {
                //Vérifie si la valeur est numérique
                if(is_numeric($value) != 1)
                {
                    //Chaine avec les colonnes ayant un problème de type
                    $non_type .= "$column($type),";
                    //Passe le booléen type à false pour empêcher l'insertion de la requête
                    $bool_type = false;
                    //Compte le nombre d'erreurs de type
                    $cpt_non_type++;
                }
            }
        }
    }
    //Récupération de tous les champs
    $verif = array($non_type,$cpt_non_type,$bool_type);
    return $verif;
}

//-------------------------------TABLE AIRCRAFTS--------------------------------------
function liste_deroulante($db,$find_table,$find_colonne)
{
    //Prend que la colonne demandé, enlèves les doublons et les valeurs nulles
    $requete = $db->prepare("SELECT DISTINCT $find_colonne as colonne FROM $find_table WHERE $find_colonne !='' ORDER BY $find_colonne ASC");
    $requete->execute();
    $liste_deroulante = $requete->fetchAll();
    return $liste_deroulante;
}

//-------------------------------TABLE CALLSIGNSROUTES--------------------------------------
function ajouter($db,$callsign,$fromairporticao,$toairporticao,$fromairportiata,$toairportiata)
{
    $user = $_SESSION['username'];
    //Instanciation de routeiata, routeicao, de la requete operator et de la requete complete
    $routeiata="";
    $routeicao="";
    $final_request="";
    $operator_request="";
    //Récupère uniquement les informations de ces champs la pour effectuer les requêtes
    $liste_dependances = array("fromairporticao","fromairportiata","toairporticao","toairportiata");
    foreach($liste_dependances as $colonne)
    {
        //Récupère les informations dans l'URL
        $value = $_GET[$colonne] ?? "";
        //Gestion des variables
        if($fromairportiata !="" && $value == $fromairporticao){$value="";}
        if($toairportiata!="" && $value == $toairporticao){$value="";}
        if($value ==""){continue;}
        //Modification des champs pour faire la requête dans la table airports
        $list = array("fromairport","toairport");
        $colonne = str_ireplace($list,"",$colonne);
        //Liste des champs à récupérer
        $liste_champ = array("icao","iata","airport","latitude","longitude","altitude","ville","pays");
        //Requete pour récuperer les informations
        $requete = $db->prepare("SELECT icao,iata,airport,latitude,longitude,altitude,ville,pays FROM airports WHERE $colonne = '$value'");
        $requete->execute();
        $tuples = $requete->fetchAll();
        foreach($tuples as $tuple)//Pour lire la requete
        {
            $iata = $tuple['iata'];
            $icao = $tuple['icao'];
            //Instanciation de routeiata et routeicao
            $routeiata .= "$iata-";
            $routeicao .= "$icao-";
            foreach($liste_champ as $champ)//Pour lire tous les champs de la requete un à un 
            {
                $value = $tuple[$champ];
                $final_request .= "'".$value."',";
            }
        }
    }
    //Récupération des champs (operatoricao,operatoriata,operatorname)
    $operator_icao = substr($callsign,0,3);//3ers caractères du callsign
    $liste_champ = array("icao","iata","designation");//Liste des champs
    $requete = $db->prepare("SELECT icao,iata,designation FROM socair WHERE icao = '$operator_icao'");//Requete
    $requete->execute();
    $tuples = $requete->fetchAll();
    //Instanciation des variables en cas de valeur null
    $operator_icao = "";
    $operator_iata = "";
    $operator_designation = "";
    foreach($tuples as $tuple)
    {
        $operator_icao = $tuple['icao'] ?? "";
        $operator_iata = $tuple['iata'] ?? "";
        $operator_designation = $tuple['designation'] ?? "";
    }
    $operator_request = "'$operator_icao','$operator_iata','$operator_designation'";//Concatène les valeurs
    //Finalisation de la requête
    $final_request = rtrim($final_request,",");//Enlève la virgule à la fin
    $routeicao = rtrim($routeicao,"-");//Enlève le tirer à la fin
    $routeiata = rtrim($routeiata,"-");//Enlève le tirer à la fin
    $current_date = date("Y-m-d H:i:s");//Récupère la date à l'instant t
    $final_request = "'$callsign','$routeicao','$routeiata',$operator_request,'',$final_request,0,0,'$current_date','$user'";
    return $final_request;
}

function modifier($db,$callsign,$fromairporticao,$toairporticao,$fromairportiata,$toairportiata)
{
    $user = $_SESSION['username'];
    //Instanciation de routeiata, routeicao, de la requete operator et de la requete complete
    $routeiata="";
    $routeicao="";
    $final_request="";
    $operator_request="";
    //Récupère uniquement les informations de ces champs la pour effectuer les requêtes
    $liste_dependances = array("fromairporticao","fromairportiata","toairporticao","toairportiata");
    //Tableau d'équivalence
    $tab_from = array(
        "fromairporticao" => "icao",
        "fromairportiata" => "iata",
        "fromairportname" => "airport",
        "fromairportlatitude" => "latitude",
        "fromairportlongitude" => "longitude",
        "fromairportaltitude" => "altitude",
        "fromairportlocation" => "ville",
        "fromairportcountry" => "pays",
    );
    $tab_to = array(
        "toairporticao" => "icao",
        "toairportiata" => "iata",
        "toairportname" => "airport",
        "toairportlatitude" => "latitude",
        "toairportlongitude" => "longitude",
        "toairportaltitude" => "altitude",
        "toairportlocation" => "ville",
        "toairportcountry" => "pays",
    );
    foreach($liste_dependances as $colonne)
    {
        //Récupère les informations dans l'URL
        $value = $_GET[$colonne] ?? "";
        //Gestion des variables
        if($fromairportiata !="" && $value == $fromairporticao){$value="";}
        if($toairportiata!="" && $value == $toairporticao){$value="";}
        if($value ==""){continue;}
        //Modification des champs pour faire la requête dans la table airports
        $list = array("fromairport","toairport");
        $search = str_ireplace($list,"",$colonne);
        //Requete pour récuperer les informations
        $requete = $db->prepare("SELECT icao,iata,airport,latitude,longitude,altitude,ville,pays FROM airports WHERE $search = '$value'");
        $requete->execute();
        $tuples = $requete->fetchAll();
        foreach($tuples as $tuple)
        {
            $iata =$tuple['iata'];
            $icao =$tuple['icao'];
            //Instanciation de routeiata et routeicao
            $routeiata .= "$iata-";
            $routeicao .= "$icao-";
        }
        //Si c'est la requete pour from alors tableau d'equivalance from
        if(strpos($colonne,"from")=="true")
        {
            foreach($tuples as $tuple)
            {
                foreach($tab_from as $call => $airport)//Parcourt le tableau d'équivalence
                {
                    $value = $tuple[$airport];//Récupère la valeur grace au nom de colonne de la table airport
                    $call = str_ireplace($call,"$call='$value'",$call);
                    $final_request .= "$call,";
                }
            }
        }
        //Si c'est la requete pour to alors tableau d'equivalance from
        if(strpos($colonne,"to")=="true")
        {
            foreach($tuples as $tuple)
            {
                foreach($tab_to as $call => $airport)//Parcourt le tableau d'équivalence
                {
                    $value = $tuple[$airport];//Récupère la valeur grace au nom de colonne de la table airport
                    $call = str_ireplace($call,"$call='$value'",$call);//
                    $final_request .= "$call,";
                }
            }
        }
    }
    //Récupération des champs (operatoricao,operatoriata,operatorname)
    $operator_icao = substr($callsign,0,3);//3ers caractères du callsign
    $requete = $db->prepare("SELECT icao,iata,designation FROM socair WHERE icao = '$operator_icao'");//Requete
    $requete->execute();
    $tuples = $requete->fetchAll();
    //Instanciation des variables en cas de valeur null
    $operator_icao = "";
    $operator_iata = "";
    $operator_designation = "";
    foreach($tuples as $tuple)
    {
        $operator_icao = $tuple['icao'] ?? "";
        $operator_iata = $tuple['iata'] ?? "";
        $operator_designation = $tuple['designation'] ?? "";
    }
    $operator_request = "operatoricao='$operator_icao',operatoriata='$operator_iata',operatorname='$operator_designation'";//Concatène les valeurs
    //Finalisation de la requête
    $final_request = rtrim($final_request,",");//Enlève la virgule à la fin
    $routeicao = rtrim($routeicao,"-");//Enlève le tirer à la fin
    $routeiata = rtrim($routeiata,"-");//Enlève le tirer à la fin
    $current_date = date("Y-m-d H:i:s");//Récupère la date à l'instant t
    $final_request = "callsign='$callsign',routeicao='$routeicao',routeiata='$routeiata',$operator_request,flightnumber='',$final_request,nbroutestopsequences=0,vrsrouteid=0,updated='$current_date',updatedby='$user'";
    return $final_request;
}


?>

