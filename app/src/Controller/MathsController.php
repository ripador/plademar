<?php
namespace App\Controller;

use App\Form\LevelType;
use App\Form\SerieType;
use App\Service\Level;
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

            $number_array = Maths::generateRandomList($levels[$d]['length'], $levels[$d]['min'],
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

            $serie = Maths::generateSerie($ini, $length, $levels[$d]['steps'][$step]);
            $gaps = Maths::generateGaps($serie, $levels[$d]['gaps']);

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
            $pass = $this->validateSerie($request, $form, 'series');
        }

        return $this->render('maths/default.html.twig', [
            'messages_key' => 'maths.series',
            'levelForm' => $levelForm->createView(),
            'form' => isset($form) ? $form->createView() : null,
            'form_generated' => (isset($serie) && $serie != null),
            'pass' => $pass ?? null,
            'streak' => $streak ?? $levelService->getStreak('series'),
        ]);
    }

    /**
     * validateSerie.
     * This method checks the if the form response is correct, comparing the filled gaps with the original serie.
     *
     * @param Request $request
     * @param \Symfony\Component\Form\FormInterface $form
     * @return bool
     */
    private function validateSerie($request, $form, $name) {
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
            $levelService = new Level($request->getSession());
            $levelService->addStreak($name);
        }

        return $pass;
    }

    /**
     * continueFrom.
     * This exercice is like a serie generated in another way. The form and checking is the same.
     *
     * @Route("/continueFrom", name="maths_continueFrom")
     * @param Request $request
     * @return Response
     */
    public function continueFrom(Request $request)
    {
        $levelService = new Level($request->getSession());
        $levels = $levelService->getMathsContinueFromLevels();

        $levelForm = $this->createForm(LevelType::class, null, ['levels' => $levels]);
        $levelForm->handleRequest($request);

        $form = $this->createForm(SerieType::class);
        $form->handleRequest($request);

        if ($levelForm->isSubmitted() && $levelForm->isValid()) {
            // Generate the numeric serie based on the selected level
            $d = $levelForm->getData()['difficult'];
            $levelService->setLevel('continueFrom', $d);
            $tail = $levels[$d]['tail'] ?? null;

            $start = Maths::rand($levels[$d]['from_low'], $levels[$d]['from_top'], $tail);
            $serie = Maths::generateContinueFrom($start, $levels[$d]['length']);

            //Create the gaps in all positions except the first
            $list = $gaps = [];
            foreach ($serie as $k => $v) {
                if (count($list) == 0) {
                    $list[$k] = $v;
                } else {
                    $list[$k] = null;
                    $gaps[$k] = $v;
                }
            }

            // Create again the form with the inputs generated
            $form = $this->createForm(SerieType::class, ['serie' => $list, 'gaps' => json_encode($gaps)]);

        } elseif ($form->isSubmitted() && $form->isValid()) {
            $levelForm->get('difficult')->setData($levelService->getLevel('continueFrom'));

            $pass = $this->validateSerie($request, $form, 'continueFrom');
        }

        return $this->render('maths/default.html.twig', [
            'messages_key' => 'maths.continueFrom',
            'levelForm' => $levelForm->createView(),
            'form' => isset($form) ? $form->createView() : null,
            'form_generated' => (isset($serie) && $serie != null),
            'pass' => $pass ?? null,
            'streak' => $streak ?? $levelService->getStreak('continueFrom'),
        ]);
    }
}