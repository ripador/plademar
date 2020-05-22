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
    public static function generateRandomList($length, $min, $max, $shuffle = false) : array
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
    public static function generateSerie($ini, $len, $step) : array
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
    public static function generateGaps($serie, $num_gaps) : array
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

    /**
     * rand.
     * Generate a random number between the $min and $max.
     * If $tail is provided, return only numbers that finish with $tail.
     *
     * @param int $min
     * @param int $max
     * @param int $tail Start only with numbers finishing with this
     * @return int
     */
    public static function rand($min, $max, $tail = null) {
        do {
            $num = rand($min, $max);
        } while ($tail != null && !preg_match('/\d*' . $tail . '$/', $num));

        return $num;
    }
    /**
     * generateContinueFrom.
     * Create a continuous serie of +1 from the start.
     *
     * @param int $start
     * @param int $length
     * @return array
     */
    public static function generateContinueFrom($start, $length) : array
    {
        $serie = [$start];
        while (count($serie) <= $length) {
            $serie[] = ++$start;
        }
        return $serie;
    }

    /**
     * generateOperation.
     *
     * @param int $min
     * @param int $max
     * @param array $strategy The strategy parameters to apply
     * @param int $num_operands Number of operators
     * @return array
     * @throws \Exception
     */
    public static function generateOperation($min, $max, $strategy, $num_operands = 2)
    {
        $operands = [];
        $result = null;

        $operators = isset($strategy['operators']) ? $strategy['operators'] : ['+'];
        $operator = $operators[array_rand($operators, 1)];
        $operand_multiplier = isset($strategy['operand_multiplier']) ? $strategy['operand_multiplier'] : 1;

        for ($i=0; $i<$num_operands; $i++) {
            $operands[$i] = rand($min, $max) * $operand_multiplier;

            switch ($operator) {
                case '+':
                    $result += $operands[$i];
                    break;
                case '-':
                    $result -= $operands[$i];
                    break;
                default:
                    throw new \Exception('Operator not supported');
            }
        }

        return [
            'operands' => $operands,
            'operator' => $operator,
            'result' => $result,
            'response' => null,
        ];
    }

    /**
     * generateOperations.
     *
     * @param int $num
     * @param int $min
     * @param int $max
     * @param array $strategies Available stretegies to pick one for each generated operation
     * @return array
     * @throws \Exception
     */
    public static function generateOperations($num, $min, $max, $strategies = [])
    {
        $operations = [];

        for ($i=0; $i<$num; $i++) {
            $strategy = $strategies[array_rand($strategies, 1)];
            $operations[$i] = self::generateOperation($min, $max, $strategy);
        }

        return $operations;
    }
}