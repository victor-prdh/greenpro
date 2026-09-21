<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\Enum\MaterialStatusEnum;
use App\Entity\Material;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MaterialType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, ['label' => 'Nom'])
            ->add('reference', TextType::class, ['label' => 'Référence'])
            ->add('dailyPrice', MoneyType::class, ['label' => 'Prix journalier', 'currency' => 'EUR'])
            ->add('status', EnumType::class, [
                'label' => 'Statut',
                'class' => MaterialStatusEnum::class,
                'choice_label' => static fn (MaterialStatusEnum $status): string => $status->trans(),
            ])
            ->add('submit', SubmitType::class, ['label' => 'Enregistrer'])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Material::class,
        ]);
    }
}
