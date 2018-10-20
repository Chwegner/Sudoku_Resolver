<?php

if (phpversion() < '7') {               // Nachbau der Funktion intdiv()
    require_once 'php7.php';
}

/**
 * Spielfelder und ihre Methoden, die für das Sudoku benötigt werden
 *
 */
class Spielfeld {

    /**
     * Zweidimensionales Array (Matrix 9x9) für ein Sudoku
     * @var array(array())
     */
    protected $feld;

    /**
     * Füllt das Spielfeld mit den Anfangswerten
     * @param mixed $anfangswert (string oder array(array()))
     * string füllt alle Felder mit dem gleichen Wert
     * oder eine Matrix 9x9 mit den Anfangswerten für jedes Feld
     */
    public function __construct($anfangswert) {
        if (is_string($anfangswert)) {
            for ($z=0; $z<9; $z++) {
                for ($s=0; $s<9; $s++) {
                    $this->feld[$z][$s] = $anfangswert;
                }
            }
        } else {
            $this->feld = $anfangswert;
        }
    }

    /**
     * Anzahl der gefüllten Zellen einer Matrix
     * @return int
     */
    public function anzahl_werte() {
        $anzahl = 0;
        foreach ($this->feld as $row) {
            foreach ($row as $cell) {
                if (!empty($cell)) {
                    $anzahl++;
                }
            }
        }
        return $anzahl;
    }
    /**
     * Ist die Lösung richtig (d.h. Zahl maximal 1x in Zeile / Spalte / Block)?
     * Die Vollständigkeit wird nicht geprüft!
     * @return boolean
     */
    public function korrekt() {
        $korrekt = true;
        // Zeilen, Spalten und Blöcke prüfen
        for ($i = 0; $i < 9; $i++) {
            if (!self::element_ok($this->zeile($i))
                    || !self::element_ok($this->spalte($i)) 
                    || !self::element_ok($this->block($i))) {
                $korrekt = false;
            }
        }
        return $korrekt;
    }
    /**
     * Handelt es sich beim dem Kandidaten $zahl um einen versteckten Einer?
     * D.h. kommt die Zahl genau 1x in einem Array (Zeile oder Spalte oder Block) vor?
     * @param string $zahl
     * @param int $row
     * @param int $col
     * @return boolean
     */
    public function kandidat_einmal($zahl, $row, $col) {
        return self::einmal($this->zeile($row), $zahl) 
                || self::einmal($this->spalte($col), $zahl)
                || self::einmal($this->block(self::blocknr($row, $col)), $zahl);
    }
    /**
     * entfernt die Lösungszahl aus dem Kandidaten-Spielfeld
     * @param string $zahl
     * @param type $row
     * @param type $col
     */
    public function entferne($zahl, $row, $col) {
        $this->feld[$row][$col] = '';
        // ggf. Kandidaten aus den Zeilen entfernen
        for ($k = 0; $k < 9; $k++) {
            $this->feld[$row][$k] = str_replace($zahl, '', $this->feld[$row][$k]);
        }
        // ggf. Kandidaten aus den Spalten entfernen
        for ($k = 0; $k < 9; $k++) {
            $this->feld[$k][$col] = str_replace($zahl, '', $this->feld[$k][$col]);
        }
        // Grundkoordinaten für betreffenden Block bestimmen (linke obere Ecke)
        $row -= $row % 3;
        $col -= $col % 3;
        // ggf. Kandidaten aus den Blöcken entfernen
        for ($j = 0; $j < 3; $j++) {
            for ($i = 0; $i < 3; $i++) {
                $this->feld[$row + $j][$col + $i] = str_replace($zahl, '', $this->feld[$row + $j][$col + $i]);
            }
        }
    }
    public function getCell($row, $col) {
        return $this->feld[$row][$col];
    }
    public function setCell($zahl, $row, $col) {
        $this->feld[$row][$col] = $zahl;
    }

    /**
     * Gibt die Zeile $index aus dem Spielfeld als eindimensionaler Array zurück
     * @param int $index
     * @return array
     */
    private function zeile($index) {
        return $this->feld[$index];
    }
    /**
     * Gibt die Spalte $index aus dem Spielfeld als eindimensionaler Array zurück
     * @param int $index
     * @return array
     */
    private function spalte($index) {
        return array_column($this->feld, $index);
    }
    /**
     * Gibt den Block $index aus dem Spielfeld als eindimensionaler Array zurück
     * @param int $index
     * @return array
     */
    private function block($index) {
        // Grundkoordinaten für betreffenden Block bestimmen (linke obere Ecke)
        $row = $index - ($index % 3);
        $col = $index % 3;
        return array_merge(
                array_slice($this->feld[$row + 0], $col * 3, 3), 
                array_slice($this->feld[$row + 1], $col * 3, 3), 
                array_slice($this->feld[$row + 2], $col * 3, 3)
        );
    }
    /**
     * Ermittelt die Blocknummer aus Zeilennummer und Spaltennummer
     * @param int $row
     * @param int $col
     * @return int
     */
    static private function blocknr($row, $col) {
        $row -= $row % 3;
        $col = intdiv($col, 3);
        return $row + $col;
    }
    /**
     * Kommt jede Zahl maximal 1x im Array (Zeile oder Spalte oder Block) vor ?
     * @param array $element
     * @return boolean
     */
    static private function element_ok($element) {
        // Häufigkeitstabelle erstellen
        $anzahl = array_count_values($element);
        // Element mit den Leerstrings entfernen
        unset($anzahl['']);
        return (empty($anzahl) || max($anzahl) <= 1);
    }
    /**
     * Kommt die Zahl genau 1x im Array (Zeile oder Spalte oder Block) vor ?
     * @param array $element
     * @param string $zahl
     * @return boolean
     */
    static private function einmal($element, $zahl) {
        return (substr_count(implode('', $element), $zahl) === 1);
    }
}
