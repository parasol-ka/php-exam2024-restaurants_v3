<?php
    include "_connexionBD.php";

    // Dans cette partie de code les requetes SQL sont preparées et executées

    $reqTopBurgers=$bd->prepare("SELECT b.id_burger, b.nom, b.prix, b.stock, GROUP_CONCAT(i.nom) AS ingredients FROM burgers AS b JOIN liste_ingredients AS li ON b.id_burger=li.id_burger JOIN ingredients AS i ON li.id_ingredient=i.id_ingredient GROUP BY b.id_burger ORDER BY b.stock DESC LIMIT 10;");
    $reqTopBurgers->execute();
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
                }

            ?>
        </div>
    </main>
</body>
</html>