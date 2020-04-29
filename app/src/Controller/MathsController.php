<?php
namespace App\Controller;

use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * Class MathsController
 * @Route("/maths")
 */
class MathsController extends AbstractController
{
    /**
     * sort.
     *
     * @Route("/sort", name="maths_sort")
     * @param Request $request
     * @return Response
     */
    public function sort(Request $request)
    {
        /* To define de difficulty levels use this array. The 'name' is shown in the selector.
         * 'max' is the highest possible number to appear.
         * 'rows' *4 will be the number of numbers to sort.
         *
         * If you define a max < rows*4 the page will not load. */
        $levels = [
            ['max' => 12, 'rows' => 1, 'name' => 'Easy'],
            ['max' => 30, 'rows' => 2, 'name' => 'Medium'],
            ['max' => 99, 'rows' => 3, 'name' => 'Hard'],
        ];
        $choices = $this->getChoicesFromLevels($levels);

        $number_array = [];

        $form = $this->createFormBuilder()
            ->add('difficult', ChoiceType::class, [
                'label'   => 'Difficulty level',
                'choices' => $choices
            ])->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $d = $form->getData()['difficult'];

            $max = $levels[$d]['max'];
            $rows = $levels[$d]['rows'];

            do {
                $ran_num = rand(1, $max);
                if (!in_array($ran_num, $number_array)) {
                    $number_array[] = $ran_num;
                }
            } while (count($number_array) < (4 * $rows));

            //get the elements in random order
            shuffle($number_array);
        }

        return $this->render('maths/sort.html.twig', [
            'numbers' => $number_array,
            'form'    => $form->createView()
        ]);
    }

    /**
     * getChoicesFromLevels.
     *
     * @param array $levels
     * @return array
     */
    private function getChoicesFromLevels($levels)
    {
        $choices = $number_array = [];
        foreach ($levels as $k => $data) {
            $choices[$data['name']] = $k;
        }

        return $choices;
    }

    /**
     * series.
     *
     * @Route("/series", name="maths_series")
     * @param Request $request
     * @return Response
     */
    public function series(Request $request)
    {
        $levels = [
            ['name' => 'Easy', 'max' => 9],
            ['name' => 'Medium', 'max' => 99],
            ['name' => 'Hard', 'max' => 999],
        ];
        $choices = $this->getChoicesFromLevels($levels);
        $levelForm = $this->createFormBuilder()
            ->add('difficult', ChoiceType::class, [
                'label'   => 'Difficulty level',
                'choices' => $choices
            ])->getForm();

        $levelForm->handleRequest($request);
        if ($levelForm->isSubmitted() && $levelForm->isValid()) {
            $d = $levelForm->getData()['difficult'];


        }

        return $this->render('maths/series.html.twig', [
            'levelForm'    => $levelForm->createView()
        ]);
    }
}