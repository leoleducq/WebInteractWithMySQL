<!DOCTYPE html>
<html>
<?php
session_start();
if(isset($_POST['username']) && isset($_POST['password']))
{
    //------------------Appel des fonctions--------------------
    require "module/function.php";
    //Connexion à la BDD
    $db = connect_user();
    $username = $_POST['username'];
    $password = $_POST['password'];
    if($username !== "" && $password !== "")
    {
        $requete = $db->prepare("SELECT count(*) FROM utilisateurs where 
              username = '$username' and password = password('$password')");
        $requete->execute();
        $reponse = $requete->fetchAll();
        foreach($reponse as $line)
        {
            $count = $line['count(*)'];
        }
        if($count!=0) // nom d'utilisateur et mot de passe correctes
        {
           $_SESSION['username'] = $username;
           header('Location: index.php');
        }
    }
}
// mysqli_close($db); // fermer la connexion
?>
    <head>
       <meta charset="utf-8">
        <!-- importer le fichier de style -->
        <link rel="stylesheet" href="style/login.css" media="screen" type="text/css" />
    </head>
    <body>
        <div id="container">
            <!-- zone de connexion -->
            
            <form action="login.php" method="POST">
                <h1>Connexion</h1>
                
                <label><b>Nom d'utilisateur</b></label>
                <input type="text" placeholder="Entrer le nom d'utilisateur" name="username" required>

                <label><b>Mot de passe</b></label>
                <input type="password" placeholder="Entrer le mot de passe" name="password" required>

                <input type="submit" id='submit' value='LOGIN' >
                <?php
                    $username = $_POST['username'] ??"";
                    $password = $_POST['password'] ??"";
                    if($username=="" || $password=="" || $count=0)
                    {
                        echo "<p style='color:red'>Utilisateur ou mot de passe incorrect</p>";
                    }
                ?>
            </form>
        </div>
    </body>
</html>