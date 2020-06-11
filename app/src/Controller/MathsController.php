<?php
namespace App\Controller;

use App\Form\LevelType;
use App\Form\OperationsType;
use App\Form\SerieType;
use App\Service\Level;
use Symfony\Component\Form\FormError;
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
        list($levelForm, $levels) = $this->createLevelForm($request, 'series');

        $form = $this->createForm(SerieType::class);
        $form->handleRequest($request);

        if ($levelForm->isSubmitted() && $levelForm->isValid()) {
            // Generate the numeric serie based on the selected level
            $d = $levelForm->getData()['difficult'];
            $levelService->setLevel('series', $d);

            $step = array_rand($levels[$d]['steps']);
            $serie = Maths::generateSerie($levels[$d]['lowest'], $levels[$d]['highest'], $levels[$d]['length'], $levels[$d]['steps'][$step]);
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
     * @param string $name
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
     * createLevelForm.
     * Get the difficulty levels for the exercice with given name and create the form.
     *
     * @param Request $request
     * @param string $exercice
     * @return array
     */
    private function createLevelForm(Request $request, $exercice)
    {
        $levelService = new Level($request->getSession());
        $levels = $levelService->getLevelForExercice($exercice);

        $levelForm = $this->createForm(LevelType::class, null, ['levels' => $levels]);
        $levelForm->handleRequest($request);

        return [$levelForm, $levels];
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
        list($levelForm, $levels) = $this->createLevelForm($request, 'ContinueFrom');

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

    /**
     * @Route("/strategies", name="maths_strategies")
     * @param Request $request
     * @return Response
     */
    public function strategies(Request $request)
    {
        $levelService = new Level($request->getSession());
        list($levelForm, $levels) = $this->createLevelForm($request, 'strategies');

        $form = $this->createForm(OperationsType::class);
        $form->handleRequest($request);

        if ($levelForm->isSubmitted() && $levelForm->isValid()) {
            // Generate the numeric serie based on the selected level
            $d = $levelForm->getData()['difficult'];
            $levelService->setLevel('strategies', $d);

            try {
                $operations = Maths::generateOperations(
                    $levels[$d]['num'],
                    $levels[$d]['min'], $levels[$d]['max'],
                    $levels[$d]['strategies']
                );
                $form = $this->createForm(OperationsType::class, ['operations' => $operations]);
            } catch (\Exception $e) {
                $this->addFlash('danger', $e->getMessage());
            }

        } elseif ($form->isSubmitted() && $form->isValid()) {
            $d = $levelService->getLevel('strategies');
            $levelForm->get('difficult')->setData($d);

            list($passes, $checks) = $this->validateOperations($request, $form, 'strategies');
        }

        return $this->render('maths/default.html.twig', [
            'messages_key' => 'maths.strategies',
            'levelForm' => $levelForm->createView(),
            'form' => isset($form) ? $form->createView() : null,
            'form_generated' => (isset($operations) && $operations != null),
            'pass' => (isset($passes) && isset($checks)) ? ($passes == $checks) : null,
            'passes' => $passes ?? null,
            'checks' => $checks ?? null,
            'streak' => $streak ?? $levelService->getStreak('strategies'),
            'levelParams' => (isset($levels) && isset($d)) ? $levels[$d] : null,
            'javascripts' => [
                'operations.js'
            ]
        ]);
    }

    /**
     * validateOperations.
     *
     * @param Request $request
     * @param \Symfony\Component\Form\FormInterface $form
     * @param string $exercice
     * @return array
     */
    private function validateOperations(Request $request, $form, $exercice)
    {
        $checks = $passes = 0;
        $operations = $form->get('operations')->getData();
        foreach ($operations as $i => $operation) {
            $checks++;
            if ((float) $operation['result'] != (float) $operation['response']) {
                $pass = false;
                $form->get('operations')[$i]->get('response')->addError(new FormError('lol'));
            } else {
                $passes++;
            }
        }

        if ($checks == $passes) {
            $levelService = new Level($request->getSession());
            $levelService->addStreak($exercice);
        }
        return [$passes, $checks];
    }

}