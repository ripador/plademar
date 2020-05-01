<?php
namespace App\Controller;

use App\Form\SerieType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Service\Maths;

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
            'levelForm' => $form->createView()
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
     * Generate random numeric series that students have to complete.
     *
     * @Route("/series", name="maths_series")
     * @param Request $request
     * @return Response
     */
    public function series(Request $request)
    {
        $levels = [
            ['name' => 'Easy',   'length' => 3,  'gaps' => 1, 'lowest' => 1, 'highest' => 6,   'steps' => [1]],
            ['name' => 'Medium', 'length' => 6,  'gaps' => 2, 'lowest' => 0, 'highest' => 99,  'steps' => [2, 4, 5]],
            ['name' => 'Hard',   'length' => 10, 'gaps' => 4, 'lowest' => 0, 'highest' => 999, 'steps' => [3, 5, 10]],
        ];
        $choices = $this->getChoicesFromLevels($levels);
        $levelForm = $this->createFormBuilder()
            ->add('difficult', ChoiceType::class, [
                'label'   => 'Difficulty level',
                'choices' => $choices
            ])->getForm();
        $levelForm->handleRequest($request);

        $form = $this->createForm(SerieType::class);
        $form->handleRequest($request);

        if ($levelForm->isSubmitted() && $levelForm->isValid()) {
            $d = $levelForm->getData()['difficult'];

            // Generate the numeric serie
            $ini = rand($levels[$d]['lowest'], $levels[$d]['highest']);
            $length = $levels[$d]['length'];
            $step = array_rand($levels[$d]['steps']);

            $mathsService = new Maths();
            $serie = $mathsService->generateSerie($ini, $length, $levels[$d]['steps'][$step]);
            $gaps = $mathsService->generateGaps($serie, $levels[$d]['gaps']);

            $list = [];
            foreach ($serie as $k => $v) {
                $list[$k] = array_key_exists($k, $gaps) ? null : $v;
            }

            $form = $this->createForm(SerieType::class, ['serie' => $list, 'gaps' => json_encode($gaps)]);

        } elseif ($form->isSubmitted() && $form->isValid()) {
            $formData = $form->getData();
            $serie = $formData['serie'];
            $gaps = json_decode($formData['gaps']);

            $pass = true;
            foreach ($gaps as $k => $v) {
                if ($serie[$k] != $v) {
                    $pass = false;
                    break;
                }
            }
        }

        return $this->render('maths/series.html.twig', [
            'levelForm' => $levelForm->createView(),
            'serie' => $serie ?? null,
            'gaps' => $gaps ?? null,
            'form' => isset($form) ? $form->createView() : null,
            'pass' => isset($pass) ? $pass : null,
        ]);
    }
}