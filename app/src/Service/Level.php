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

    public function getMathsSeriesLevels() : array
    {
        return [
            ['name' => 'Easy',   'length' => 3,  'gaps' => 1, 'lowest' => 1, 'highest' => 6,   'steps' => [1]],
            ['name' => 'Medium', 'length' => 6,  'gaps' => 2, 'lowest' => 0, 'highest' => 99,  'steps' => [2, 4, 5]],
            ['name' => 'Hard',   'length' => 10, 'gaps' => 4, 'lowest' => 0, 'highest' => 999, 'steps' => [3, 5, 10]],
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
}