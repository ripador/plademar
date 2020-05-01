<?php
namespace App\Service;

class Maths
{
    /**
     * generateSerie.
     *
     * @param int $ini Initial number
     * @param int $len Length of the serie
     * @param int $step Number to add in each iteration
     * @return array
     */
    public function generateSerie($ini, $len, $step) : array
    {
        $serie = [];
        $num = $ini;
        while (count($serie) < $len) {
            $num+=$step;
            $serie[] = $num;
        }

        return $serie;
    }

    /**
     * generateGaps.
     * Create the gaps, removing random positions from the serie.
     *
     * @param array $serie
     * @param int $num_gaps
     * @return array
     */
    public function generateGaps($serie, $num_gaps) : array
    {
        $gap_pos = array_rand($serie, $num_gaps);

        if ($num_gaps == 1) {
            $gaps[$gap_pos] = $serie[$gap_pos];
        } else {
            $gaps = [];
            foreach ($gap_pos as $one_gap_pos) {
                $gaps[$one_gap_pos] = $serie[$one_gap_pos];
            }
        }
        return $gaps;
    }
}