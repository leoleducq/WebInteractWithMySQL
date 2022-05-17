//Récupération pour aircrafts
function change(valeur)
{
    //Récupération des valeurs transmises
    valeur = valeur.value;
    colonne = event.target.id;
    //Enlever le liste. de colonne
    colonne = colonne.replace("liste.","")
    //Changer la valeur dans le html
    document.getElementById(colonne).value = valeur;
}


//Grise les input de callsignsroutes
function toggleFile(element)
{
    // console.log(element.id);
    var fromairporticao = document.getElementById("fromairporticao");
    var toairporticao = document.getElementById("toairporticao");
    var fromairportiata = document.getElementById("fromairportiata");
    var toairportiata = document.getElementById("toairportiata");
    if(element.id=="fromairporticao") {fromairportiata.value=""}
    if(element.id=="fromairportiata") {fromairporticao.value=""}
    if(element.id=="toairporticao") {toairportiata.value=""}
    if(element.id=="toairportiata") {toairporticao.value=""}
}
