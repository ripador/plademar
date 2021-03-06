<?php
namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;

class OperationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('operands', CollectionType::class, [
                'entry_type' => NumberType::class,
                'entry_options' => [
                    'attr' => ['readonly' => 'readonly', 'tabindex' => '-1']
                ],
                'allow_add' => true
            ])
            ->add('response', NumberType::class, ['required' => false]) //here they put the response
            ->add('operator', HiddenType::class)
            ->add('result', HiddenType::class) //this is the correct result of the operation
        ;
    }

    public function getName()
    {
        return 'operation';
    }
}