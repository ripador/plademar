<?php
namespace App\Service;

class Maths
{
    /**
     * generateRandomList.
     * Returns
     * @param int $length Lenght of the random array
     * @param int $min Lowest number that can appear
     * @param int $max Highest number that can appear
     * @param bool $shuffle If true shuffle the array before returning
     * @return array
     */
    public function generateRandomList($length, $min, $max, $shuffle = false) : array
    {
        $number_array = [];
        do {
            $ran_num = rand($min, $max);
            if (!in_array($ran_num, $number_array)) {
                $number_array[] = $ran_num;
            }
        } while (count($number_array) < $length);

        //get the elements in random order
        if ($shuffle) {
            shuffle($number_array);
        }

        return $number_array;
    }

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