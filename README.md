# WebInteractWithMySQL
### Description 
Fait pour intéragir plus facilement avec sa base de données MySQL.
### Pourquoi ?
Ce projet a été fait dans le but d'intéragir plus facilement avec notre BDD notamment pour nos collaborateurs.<br>
En effet, certains de nos collaborateurs se plaignaient de l'accès à la BDD ainsi que la difficulté à rentrer de nouvelles données.<br>
De plus, la tracabilité des changements / insertions était compliquée.
### Finalité
Grâce à cette solution, les données sont : 
* Vérifiées 
* Nettoyées
* Normalisées

Certains champs sont également remplis automatiquement en prenant les valeurs rentrées.<br>
Le code est évidemment adapté à nos besoins, mais est tout de même accessible à quiconque voulant intéragir plus facilement avec sa BDD.

### Version
<table>
    <thead>
        <tr>
            <th colspan="1">Web</th>
            <th colspan="2">Stockage</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>PHP</td>
            <td>MySQL</td>
            <td>MariaDB</td>
        </tr>
        <tr>
            <td>8.1.5</td>
            <td>15.1</td>
            <td>1.5.15</td>
        </tr>
    </tbody>
</table>

### Structure
├── [module](./module)  
│   ├── [function.php](./module/function.php)  
├── [script](./script)  
│   ├── [main.js](./script/main.js)  
├── [style](./style)  
│   ├── [login.css](./style/login.css)  
│   ├── [main.css](./style/main.css)  
│   ├── [modifaddsupp.css](./style/modifaddsupp.css)  
├── [.config.cfg](./.config.cfg)  
├── [README.md](./README.md)  
├── [ajouter.php](./ajouter.php)  
├── [index.php](./index.php)  
├── [login.php](./login.php)  
├── [modifier.php](./modifier.php)  
├── [specific_rules](./specific_rules)  
├── [supprimer.php](./supprimer.php)

### Installation FR
1. Copier ce répertoire : <code> git clone https://github.com/leoleducq/WebInteractWithMySQL.git </code>
2. Modifier le fichier [.config.cfg](./.config.cfg) avec les identifiants de connexion à vos BDD.
3. Il y a 2 connexions pour la BDD (Possibilité de mettre les mêmes informations pour les 2): 
* La 1ère : pour effectuer les requêtes
* La 2ème : pour les utilisateurs souhaitant se connecter<br>
` La table doit s'appeler "utilisateurs" sur la 2ème BDD et les mots de passe insérés avec la fonction : "password('votre_mot_de_passe')" `
4. Possibilité d'ajouter des règles pour chaque table dans le fichier [specific_rules](./specific_rules) si vous voulez les traiter différemment.
#### ⚠️ Point d'attention
* Si votre table n'a pas de <b>clé primaire</b> vous pourrez seulement la <b>consulter</b>, la <b>modification</b> et la <b>suppression</b> de tuples ne fonctionneront pas.
### Installation ENG
1. Clone this repository : <code> git clone https://github.com/leoleducq/WebInteractWithMySQL.git </code>
2. Modify the [.config.cfg](./.config.cfg) with the login of your DB.
3. There is 2 connexions for the DB (Possibility to have the same informations for both):
* First : to execute request
* Second : for user who want to connect<br>
` The table has to be called "utilisateurs" on the second BDD and password has to be insert with the function : "password('your_password')" `
4. You can add some rules in [specific_rules](./specific_rules) if you have some tables that you want to treat a different way.
#### ⚠️ Warning
* If your table doesn't have a <b>primary key</b> you just could <b>consult</b> it, the <b>modification</b> and the <b>suppression</b> of tuples couldn't work.
