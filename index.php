<!DOCTYPE HTML>
<html lang="de">
<head> 
    <meta http-equiv="content-type" content="text/html; charset=utf-8">
    <title>Sudoku-Resolver</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <h1>Sudoku Resolver</h1>
<?php
require_once 'php/formular.php';
require_once 'classes/Sudoku.class.php';

if (!isset($_POST['formular'])) {
        echo eingabe();                     // Formular zur Erfassug des Sudoku
} else {                                    // Formularverarbeitung
    if ($_POST['formular'] == 'laden') {
        $vorgabe = laden($_POST['datei2']);
    } else {
        $vorgabe = aus_formular();
        if ($_POST['formular'] == 'speichern') {
            speichern($vorgabe, $_POST['datei1']);
        } 
    }

    $sudoku = new Sudoku(new Spielfeld($vorgabe));
    $sudoku->solve();
    echo $sudoku->display();
}
?>
</body>
</html>
