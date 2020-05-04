<?php
namespace App\Controller;

use App\Form\LevelType;
use App\Form\SerieType;
use App\Service\Level;
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
        $levelService = new Level($request->getSession());
        $levels = $levelService->getMathsOrderLevels();
        $number_array = [];

        $levelForm = $this->createForm(LevelType::class, null, ['levels' => $levels]);
        $levelForm->handleRequest($request);
        if ($levelForm->isSubmitted() && $levelForm->isValid()) {
            $d = $levelForm->getData()['difficult'];

            $mathsService = new Maths();
            $number_array = $mathsService->generateRandomList($levels[$d]['length'], $levels[$d]['min'],
                $levels[$d]['max'], true);
        }

        return $this->render('maths/sort.html.twig', [
            'numbers' => $number_array,
            'levelForm' => $levelForm->createView()
        ]);
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
        $levelService = new Level($request->getSession());
        $levels = $levelService->getMathsSeriesLevels();

        $levelForm = $this->createForm(LevelType::class, null, ['levels' => $levels]);
        $levelForm->handleRequest($request);

        $form = $this->createForm(SerieType::class);
        $form->handleRequest($request);

        if ($levelForm->isSubmitted() && $levelForm->isValid()) {
            // Generate the numeric serie based on the selected level
            $d = $levelForm->getData()['difficult'];
            $levelService->setLevel('series', $d);

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

            // Create again the form with the inputs generated
            $form = $this->createForm(SerieType::class, ['serie' => $list, 'gaps' => json_encode($gaps)]);

        } elseif ($form->isSubmitted() && $form->isValid()) {
            //Fill the difficult on the level form from the session, to maintain in the same level
            $levelForm->get('difficult')->setData($levelService->getLevel('series'));


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

            if ($pass) {
                $streak = $levelService->addStreak('series');
                $changeLevel = ($streak >= 4);
            }
        }

        return $this->render('maths/series.html.twig', [
            'levelForm' => $levelForm->createView(),
            'serie' => $serie ?? null,
            'gaps' => $gaps ?? null,
            'form' => isset($form) ? $form->createView() : null,
            'pass' => $pass ?? null,
            'streak' => $streak ?? $levelService->getStreak('series'),
            'changeLevel' => $changeLevel ?? false,
        ]);
    }
}