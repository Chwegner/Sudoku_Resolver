<?php
/**
 * Speichert das übergebene Array im JSON-Format in $datei im Ordner raetsel
 * @param array $arr    irgendein Array
 * @param string $datei Dateiname
 */
function speichern(array $arr, $datei) {
    file_put_contents('raetsel/'.$datei, json_encode($arr));
}
/**
 * Liest ein Array im JSON-Format aus $datei im Ordner raetsel
 * @param string $datei Dateiname
 */
function laden($datei) {
    return json_decode(file_get_contents('raetsel/'.$datei));
}
/**
 * Vorgabewerte für das Sudoku aus dem Formular übernehmen
 * @return array    Matrix 9x9 aus dem Eingabeformular
 */
function aus_formular()
{
    foreach ($_POST['sudoku'] as $z => $row) {
        foreach ($row as $s => $cell) {
            $vorgabe[$z][$s] = '';
            if ((int) $cell <= 9 && (int) $cell >= 1) {
                $vorgabe[$z][$s] = (int) $cell;
           }
        }
    }
    return $vorgabe;
}
/**
 * Füllt ein Select-Feld mit den gespeicherten Sudokus im Ordner raetsel
 * @param string $name  Name des Select-Feldes
 * @return string       HTML-Select
 */
function fillSelect ($name) {
    // Füllt ein Select-Feld mit den gespeicherten Sudokus
    $feld = "<select name=\"$name\">";
    
    $verz = 'raetsel';
    if (!is_dir($verz)) {
        if (!mkdir($verz)) {
            return $feld."</select>";
        } else {
            chmod($verz, 0775);
        }
    }
    $dirh = opendir($verz);
    // Liefert am Ende false, kann mit Dateinamen 0 verwechselt werden
    while (($str = readdir($dirh)) !== false) {
        if (preg_match('~.json$~i', $str)) {
            $feld .= "<option>$str</option>";
        }
    }
    closedir($dirh);
    $feld .= "</select>";
    return $feld;
}
/**
 * Erstellt das Eingabeforumular für das Sudoku
 * @return string   HTML-Formular
 */
function eingabe()
{
    $formular = '<form action="index.php" method="post">';
    $formular .= "<fieldset><legend>Bitte ein neues Sudoko eingeben:</legend>\n";
    $formular .= '<table>';    
    for ($i=0; $i<9; $i++) {
        $formular .= '<tr>';
        for ($j=0; $j<9; $j++) {
            $formular .= '<td>';
            $formular .= "<input type=\"text\" size=\"2\" maxlength=\"1\" name=\"sudoku[$i][$j]\">";
            $formular .= '</td>';
        }
        $formular .= '</tr>';
    }
    $formular .= "</table><br>\n";    
    $formular .= '<button type="submit" name="formular" value="loesen">'
            . 'erfasstes Sudoku prüfen und lösen</button><br>';
    $formular .= '<input type="text" name="datei1" value="sudoku.json">';    
    $formular .= '<button type="submit" name="formular" value="speichern">'
            . 'erfasstes Sudoku speichern und lösen</button>'."<br>\n";    
    $formular .= fillSelect('datei2');
    $formular .= '<button type="submit" name="formular" value="laden">'
            . 'Sudoku laden und lösen</button>';
    $formular .= "</fieldset></form>\n";
    return $formular;
}
