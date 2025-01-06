<?php
    include "_connexionBD.php";

    // Dans cette partie de code les requetes SQL sont preparées et executées

    $reqTopBurgers=$bd->prepare("SELECT b.id_burger, b.nom, b.prix, b.stock, GROUP_CONCAT(i.nom) AS ingredients FROM burgers AS b JOIN liste_ingredients AS li ON b.id_burger=li.id_burger JOIN ingredients AS i ON li.id_ingredient=i.id_ingredient GROUP BY b.id_burger ORDER BY b.stock DESC LIMIT 10;");
    $reqTopBurgers->execute();

    $reqBurgersList=$bd->prepare("SELECT nom, id_burger FROM burgers ORDER BY nom");
    $reqBurgersList->execute();

    // Verification de GET 

    if(isset($_GET["burger"])){
        $id_burger_cleaned=(int)$_GET["burger"];
        if($id_burger_cleaned){
            $verifyBurger=$bd->prepare("SELECT * FROM burgers WHERE id_burger=:id_burger");
            $verifyBurger->bindvalue("id_burger", $id_burger_cleaned);
            $verifyBurger->execute();
            $burger_verification=$verifyBurger->fetch();

            if($burger_verification!=NULL){

                $reqEmployes=$bd->prepare("SELECT CONCAT(e.nom, '-', e.prenom) AS employe, SUM(v.nombre) AS ventes, ROUND(SUM(b.prix*v.nombre), 2) AS argent 
                                        FROM burgers AS b JOIN ventes AS v ON b.id_burger=v.id_burger 
                                        JOIN commandes AS c ON v.id_commande=c.id_commande 
                                        JOIN employes AS e ON c.id_employe=e.id_employe 
                                        WHERE b.id_burger=:id_burger 
                                        GROUP BY c.id_employe 
                                        ORDER BY e.nom;");
                $reqEmployes->bindvalue("id_burger", $id_burger_cleaned);
                $reqEmployes->execute();

                $reqTotal=$bd->prepare("SELECT ROUND(SUM(b.prix*v.nombre)) AS total, b.nom FROM burgers AS b 
                JOIN ventes AS v ON b.id_burger=v.id_burger 
                JOIN commandes AS c ON v.id_commande=c.id_commande 
                JOIN employes AS e ON c.id_employe=e.id_employe 
                WHERE b.id_burger=:id_burger 
                GROUP BY b.id_burger;");
                $reqTotal->bindvalue("id_burger", $id_burger_cleaned);
                $reqTotal->execute();

            }else {header("Location: index.php"); $id_burger_cleaned=NULL;}
        }else {header("Location: index.php"); $id_burger_cleaned=NULL;}
    }
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <title>Restaurants v3</title>
</head>
<body>
    <main>
        <div id="available_burgers_container">
            <?php
                while($top_burgers=$reqTopBurgers->fetch()){
                    $id_burger=$top_burgers["id_burger"];
                    $burger_image=str_pad($id_burger, 3, '0', STR_PAD_LEFT);
                    $burger_name=$top_burgers["nom"];
                    $burger_price=$top_burgers["prix"];
                    $stock=$top_burgers["stock"];
                    $ingredients_list=explode(",", $top_burgers["ingredients"]);
                    
                    echo "<div class='top_burgers_lines'><img src='images/b$burger_image.png' style='width: 60px;'>
                    <a href='index.php?burger=$id_burger' class='burgers_links'>$burger_name</a> - $burger_price € - $stock :";
                    foreach ($ingredients_list as $key => $value) {
                        echo "<img src='icones/$value.png' style='width : 50px;'>";
                    }
                        echo "</div>";
                }?>
        </div>
        <div id="burger_filter_container">
            <form action="index.php" method="get">
                <select name="burger" id="burger">
                    <?php
                        while($burgers_list=$reqBurgersList->fetch()){
                            $id_burger_select=$burgers_list["id_burger"];
                            $burger_name_select=$burgers_list["nom"];
                            
                            echo "<option name='burger' id='burger' value='$id_burger_select'>$burger_name_select</option>";
                        }
                    ?>
                </select>
                <input type="submit" value="Voir les employés">
            </form>
        </div>
        <?php
            if($id_burger_cleaned!=NULL){
                while($employes=$reqEmployes->fetch()){
                    $employe_name=$employes["employe"];
                    $burgers_sold=$employes["ventes"];
                    $total_employe=$employes["argent"];
                    
                    echo "<div id='employes_container'><p>$employe_name : $burgers_sold<br>$total_employe €</p></div>";
                }
                $total=$reqTotal->fetch();
                $total_burger_name=$total["nom"];
                $total_sum=$total['total'];
                echo "<p>Le total des ventes pour $total_burger_name est de : $total_sum €</p>"; 
            }
        ?>
    </main>
</body>
</html>