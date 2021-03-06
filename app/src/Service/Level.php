<?php
namespace App\Service;

use Symfony\Component\HttpFoundation\Session\SessionInterface;

class Level
{
    private $session;

    public function __construct(SessionInterface $session)
    {
        $this->session = $session;
    }

    public function resetStreak($name) : void
    {
        $this->session->set($name . '_streak', 0);
    }

    /**
     * @param string $name
     * @return int
     */
    public function getStreak($name) : int
    {
        return $this->session->get($name . '_streak', 0);
    }

    public function addStreak($name, $num = 1) : int
    {
        $name .=  '_streak';
        $streak = $this->session->get($name, 0);
        $streak += $num;
        $this->session->set($name, $streak);

        return $streak;
    }

    /**
     * getLevel.
     *
     * @param string $name
     * @return int
     */
    public function getLevel($name) : int
    {
        return $this->session->get($name . '_level', 0);
    }

    /**
     * setLevel.
     * Defines a level for a section/exercice, and reset the streak if the level has changed.
     *
     * @param string $name Name of the exercice
     * @param int $level
     * @return bool True if the level changes
     */
    public function setLevel($name, $level) : bool
    {
        $actualLevel = $this->session->get($name . '_level', 0);

        if ($actualLevel != $level) {
            $this->session->set($name . '_level', $level);
            $this->resetStreak($name);

            $changeLevel = true;
        }

        return $changeLevel ?? false;
    }

    /**
     * getLevelForExercice.
     *
     * @param string $name
     * @return array
     */
    public function getLevelForExercice($name) : array
    {
        $func = 'getMaths' . ucfirst($name) . 'Levels';

        return $this->$func();
    }

    /**
     * To define de difficulty levels use this array. The 'name' is shown in the selector.
     * 'max' is the highest possible number to appear.
     * 'rows' *4 will be the number of numbers to sort.
     *
     * If you define a max < rows*4 the page will not load.
     */
    public function getMathsOrderLevels() : array
    {
        return [
            ['name' => '1', 'length' => 3, 'min' => 0, 'max' => 9],
            ['name' => '2', 'length' => 4, 'min' => 0, 'max' => 20],
            ['name' => '3', 'length' => 6, 'min' => 0, 'max' => 100],
            ['name' => '4', 'length' => 8, 'min' => 0, 'max' => 1000],
            ['name' => '5', 'length' => 8, 'min' => 1000, 'max' => 9999],
            ['name' => '6', 'length' => 8, 'min' => 10000, 'max' => 99999],
            ['name' => '7', 'length' => 8, 'min' => 990000, 'max' => 999999],
        ];
    }

    /**
     * lenght: how many items has the serie
     * gaps: number of blank spaces to fill
     * lowest: minimum for initial value
     * highest: maximum for initial value
     * steps: possible values for addition or substraction
     * @return array
     */
    public function getMathsSeriesLevels() : array
    {
        return [
            ['name' => '1', 'length' => 3, 'gaps' => 1, 'lowest' => 1, 'highest' => 6,   'steps' => [1]],
            ['name' => '2', 'length' => 4, 'gaps' => 2, 'lowest' => 0, 'highest' => 9,  'steps' => [2, 4, 5]],
            ['name' => '3', 'length' => 5, 'gaps' => 2, 'lowest' => 0, 'highest' => 99, 'steps' => [3, 4, 5]],
            ['name' => '4', 'length' => 6, 'gaps' => 3, 'lowest' => 0, 'highest' => 99,   'steps' => [5, 10]],
            ['name' => '5', 'length' => 7, 'gaps' => 3, 'lowest' => 0, 'highest' => 999,  'steps' => [2, 5, 10]],
            ['name' => '6', 'length' => 8, 'gaps' => 3, 'lowest' => 0, 'highest' => 999, 'steps' => [5, 10, 15]],
            ['name' => '7', 'length' => 9, 'gaps' => 4, 'lowest' => 0, 'highest' => 999, 'steps' => [3, 5, 7, 11]],
        ];
    }

    public function getMathsContinueFromLevels() : array
    {
        return [
            ['name' => '1', 'from_low' => 0, 'from_top' => 9, 'length' => 4],
            ['name' => '2', 'from_low' => 0, 'from_top' => 19, 'length' => 4],
            ['name' => '3', 'from_low' => 11, 'from_top' => 30, 'length' => 4],
            ['name' => '4', 'from_low' => 30, 'from_top' => 99, 'length' => 4],
            ['name' => '5', 'from_low' => 100, 'from_top' => 999, 'tail' => 98, 'length' => 4],
            ['name' => '6', 'from_low' => 1000, 'from_top' => 9999, 'tail' => 98, 'length' => 4],
            ['name' => '7', 'from_low' => 10000, 'from_top' => 999999, 'tail' => 98, 'length' => 4],
        ];
    }

    public function getMathsOperationsLevels() : array
    {
        return [
            ['name' => 1, 'min' => 0, 'max' => 9, 'num' => 10, 'time' => 60, 'strategies' => [1]],
            ['name' => 2, 'min' => 0, 'max' => 20, 'num' => 20, 'time' => 90, 'strategies' => [1, 2]],
            ['name' => 3, 'min' => 0, 'max' => 100, 'num' => 30, 'time' => 120, 'strategies' => [10]],
        ];
    }

    /**
     * getMathsStrategiesLevels.
     * Definition of strategies exercices.
     * In the Maths service the operations are generated using the parameters as described here:
     * min: minimum number for an operand
     * max: maximum number for an operand
     * num: number of operations to generate
     * time: time to complete the exercice
     * strategies:
     *   operators: the operators to apply randomly
     *   operand_multiplier: number to multiply the random generated operator
     *   operand_limit: list of available operands and options
     *     list: the available operands
     *     let_free: the index where the limit does NOT apply. Ex: let_free => [0] willnot apply restrictions to first operand
     *   operand_same: if true force the operands of the operation to be equal
     *
     * @return array
     */
    public function getMathsStrategiesLevels() : array
    {
        return [
            [
                'name' => 'maths.strategies.levels.tens', 'min' => 0, 'max' => 10, 'num' => 20, 'time' => 120,
                'strategies' => [
                    [
                        'operators' => ['+', '-'],
                        'operand_multiplier' => 10
                    ]
                ]
            ],
            [
                'name' => 'maths.strategies.levels.2', 'min' => 0, 'max' => 99, 'num' => 20, 'time' => 120,
                'strategies' => [
                    [
                        'operators' => ['+', '-'],
                        'operand_limit' => [
                            'list' => [1, 2],
                            'let_free' => [0],
                        ]
                    ]
                ]
            ],
            [
                'name' => 'maths.strategies.levels.5', 'min' => 0, 'max' => 10, 'num' => 20, 'time' => 120,
                'strategies' => [
                    [
                        'operators' => ['+', '-'],
                        'operand_multiplier' => 5,
                        'operand_limit' => [
                            'list' => [1, 2],
                            'let_free' => [0],
                        ]
                    ]
                ]
            ],
            [
                'name' => 'maths.strategies.levels.doubles', 'min' => 1, 'max' => 9, 'num' => 20, 'time' => 120,
                'strategies' => [
                    [
                        'operators' => ['+'],
                        'operand_same' => true
                    ]
                ]
            ],
            [
                'name' => 'maths.strategies.levels.doubles2', 'min' => 11, 'max' => 50, 'num' => 20, 'time' => 120,
                'strategies' => [
                    [
                        'operators' => ['+'],
                        'operand_same' => true,
                        'operand_limit' => [
                            'list' => [
                                0, 1, 2, 3, 4,
                                10, 11, 12, 13, 14,
                                20, 21, 22, 23, 24,
                                30, 31, 32, 33, 34,
                                40, 41, 42, 43, 44,
                            ],
                        ]
                    ]
                ]
            ],
            [
                'name' => 'maths.strategies.levels.doubles3', 'min' => 11, 'max' => 50, 'num' => 20, 'time' => 120,
                'strategies' => [
                    [
                        'operators' => ['+'],
                        'operand_same' => true
                    ]
                ]
            ],
            [
                'name' => 'maths.strategies.levels.eq10', 'min' => 1, 'max' => 99, 'num' => 20, 'time' => 120,
                'strategies' => [
                    [
                        'operators' => ['+'],
                        'result_limit' => [1, 2, 3, 4, 5, 6, 7, 8, 9],
                        'result_multiplier' => 10
                    ]
                ]
            ],
        ];
    }

    /**
     * getMathsTablesLevels.
     *
     * @return array
     */
    public function getMathsTablesLevels(): array
    {
        return [
            [
                'name' => '1',
                'tables' => [1, 3, 5],
                'till' => 5,
                'max_ops' => 9,
            ],
            [
                'name' => '2',
                'tables' => [1, 3, 5, 10],
                'till' => 10,
                'max_ops' => 15,
            ]
        ];
    }
}