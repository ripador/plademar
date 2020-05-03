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
            ['name' => '1', 'length' => 3, 'min' => 0, 'max' => 9],
            ['name' => '2', 'length' => 4, 'min' => 0, 'max' => 20],
            ['name' => '3', 'length' => 6, 'min' => 0, 'max' => 100],
            ['name' => '4', 'length' => 8, 'min' => 0, 'max' => 1000],
            ['name' => '5', 'length' => 8, 'min' => 1000, 'max' => 9999],
            ['name' => '6', 'length' => 8, 'min' => 10000, 'max' => 99999],
            ['name' => '7', 'length' => 8, 'min' => 990000, 'max' => 999999],
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

            $mathsService = new Maths();
            $number_array = $mathsService->generateRandomList($levels[$d]['length'], $levels[$d]['min'],
                $levels[$d]['max'], true);
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
            // Generate the numeric serie based on the selected level
            $d = $levelForm->getData()['difficult'];

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
            // If the form with the serie has been submited, check the response
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