<?php

require_once 'Spielfeld.class.php';

/**
 * Sudoko-Resolver
 *
 */
class Sudoku {

    /**
     * Vorgabewerte für das Sudoku (Matrix 9x9)
     * @var Spielfeld
     */
    private $vorgabe;

    /**
     * Kandidaten für das Sudoku (Matrix 9x9)
     * @var Spielfeld
     */
    private $kandidaten;

    /**
     * Lösungswerte für das Sudoku (Matrix 9x9)
     * @var Spielfeld
     */
    private $loesung;

    /**
     * Text für die Ausgabemeldung
     * @var string 
     */
    private $text;

    /**
     * Rekursionstiefe (nur zur Info, keine Funktion!)
     * @var int 
     */
    private $tiefe;
    /**
     * Füllt die 3 Spielfelder aus den Vorgabewerten
     * @param Spielfeld $values Matrix 9x9 mit den Vorgabewerten
     */
    public function __construct(Spielfeld $values, $tiefe = 1) {
        $this->tiefe = $tiefe;
        $this->vorgabe = $values;
        $this->loesung = clone $this->vorgabe;
        $this->kandidaten = new Spielfeld('123456789');
        // Kandidaten aufgrund der Vorgabe eliminieren
        for ($z = 0; $z < 9; $z++) {
            for ($s = 0; $s < 9; $s++) {
                $cell = $this->vorgabe->getCell($z, $s);
                if (!empty($cell)) {
                    $this->kandidaten->entferne($cell, $z, $s);
                }
            }
        }
    }

    /**
     * Berechnet die Lösung des Sudoku
     * @return Spielfeld    ggf. Lösung aus der Rekursion
     */
    public function solve() {
        if ($this->vorgabe->anzahl_werte() < 17) {
            $this->text = "Sudoku hat zu wenig Ziffern oder wurde nicht geladen.";
        } else {
            if ($this->vorgabe->korrekt()) {
                $this->simple_algorithms();
                // ggf. mit Trial & Error weitermachen
                if ($this->loesung->anzahl_werte() < 9*9) {
                    $this->trial_error_algorithm();
                }
            }
            if (! $this->loesung->korrekt()) {
                $this->text = "Sudoku nicht lösbar.";
            } elseif ($this->loesung->anzahl_werte() < 9*9) {
                $this->text = "Sudoku nicht vollständig gelöst.";
            } else {
                $this->text = "Sudoku korrekt und vollständig.";
            }
        }
        return $this->loesung;  
    }

    /**
     * Liefert die HTML-Ausgabe der Lösung
     * @return string
     */
    public function display() {
        return $this->ausgabe() . $this->meldung();
    }

    /**
     * Mit diesem Algorithmus alleinkönnen leichte und mittlere Sudokus gelöst werden.
     * Die beiden Methoden, die in simple_algorithms kombiniert sind, werden 
     * solange angewendet, bis keine neuen Ergebnisse mehr gefunden werden.
     * Für die ggf. folgende rekursive Methode vermindern sie die Suchtiefe erheblich.
     */
    private function simple_algorithms() {
        do {
            $werte_vorher = $this->loesung->anzahl_werte();
            for ($z = 0; $z < 9; $z++) {
                for ($s = 0; $s < 9; $s++) {
                    $cell = $this->kandidaten->getCell($z, $s);
                    if (strlen($cell) == 1) {
                        // Methode des nackten Einers
                        // Gibt es in einer Zelle nur noch einen ein einzigen Kandidaten?
                        $this->loesung->setCell($cell, $z, $s);
                        $this->kandidaten->entferne($cell, $z, $s);
                    } else {
                        // Methode des versteckten Einers
                        // Gibt es für eine Zahl in einer Gruppe (Zeile/Spalte/Block)
                        // nur noch eine einzige mögliche Position?
                        for ($k = 0; $k < strlen($cell); $k++) {
                            if ($this->kandidaten->kandidat_einmal($cell[$k], $z, $s)) {
                                $this->loesung->setCell($cell[$k], $z, $s);
                                $this->kandidaten->entferne($cell[$k], $z, $s);
                                // Suche nach Kandidaten in dieser Zelle abbrechen
                                break;
                            }
                        }
                    }
                }
            }
            // Wiederholen, solange noch neue Werte gefunden werden
        } while ($this->loesung->anzahl_werte() != $werte_vorher);
//    echo "Ende Methode Simple - Tiefe: ".$this->tiefe." - bisher gefunden: ".$this->loesung->anzahl_werte()."<br>";
//    echo $this->ausgabe();
    }
    
    /**
     * Trial & Error
     * Mittels Rekursion wird getestet, ob ein Kandidat zu einer Lösung führt
     */
    private function trial_error_algorithm() {
//    echo "Anfang Methode T+E - bisher gefunden: ".$this->loesung->anzahl_werte();
        // Erstes Feld mit Kandidaten suchen und alle betreffenden Kandidaten merken
        for ($z = 0; $z < 9; $z++) {
            for ($s = 0; $s < 9; $s++) {
                $cell = $this->kandidaten->getCell($z, $s);
                if (!empty($cell)) {
                    break 2;
                }
            }
        }
        // ggf. alle Kandidaten für diese Zelle durchprobieren
        for ($k=0; $k<strlen($cell); $k++) {
            $test = $cell[$k];      // zu testende Zahl
//    echo " - Tiefe: ".$this->tiefe." - Zeile: $z, Saplte: $s, Kandidat: $test<br>";
            // Auf der Basise der bisherigen Lösung
            $vorgabe_next = clone $this->loesung;
            // ein neues Sudoku mit dieser Zahl als Vorgabe annehmen
            $vorgabe_next->setCell($test, $z, $s);
            $sudoku_next = new Sudoku($vorgabe_next, $this->tiefe + 1);
            $loesung_next = $sudoku_next->solve();
            if ($loesung_next->anzahl_werte() == 9*9) {
                // Lösung gefunden: Lösung übernehmen und fertig
                $this->loesung = $loesung_next;
                return;
            }
        }
    }

    /**
     * liefert eine HTML-Tabelle mit dem (bisherigen) Ergebnis
     * @return string
     */
    private function ausgabe() {
        $table = '<table>';
        for ($i = 0; $i < 9; $i++) {
            $table .= '<tr>';
            for ($j = 0; $j < 9; $j++) {
                $table .= '<td>';
                if (!empty($this->vorgabe->getCell($i, $j))) {
                    $table .= $this->vorgabe->getCell($i, $j);
                } elseif (!empty($this->loesung->getCell($i, $j))) {
                    $table .= "<strong>" . $this->loesung->getCell($i, $j) . "</strong>";
                } else {
                    $table .= "<small>" . $this->kandidaten->getCell($i, $j) . "</small>";
                }
                $table .= '</td>';
            }
            $table .= "</tr>\n";
        }
        $table .= "</table>\n";
        return $table;
    }

    /**
     * Liefert das <div>-Element mit dem Meldungstext und einem Neustartknopf zurück
     * @return string
     */
    private function meldung() {
        $meldung = '<div id="meldung"><form action="index.php" method="post">'
                . $this->text . '<br><br>'
                . '<button type="submit" name="neu">neues Spiel</button>'
                . "</form></div>\n";
        return $meldung;
    }

}
