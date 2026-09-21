<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\User;
use App\Security\Enum\RoleEnum;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                'label' => 'Email',
            ])
            ->add('roleEnums', EnumType::class, [
                'label' => 'Rôles',
                'class' => RoleEnum::class,
                'choice_label' => static fn (RoleEnum $role): string => $role->value,
                'expanded' => true,
                'multiple' => true,
                'mapped' => false,
            ])
            ->add('plainPassword', PasswordType::class, [
                'label' => $options['is_edit'] ? 'Nouveau mot de passe' : 'Mot de passe',
                'mapped' => false,
                'required' => !$options['is_edit'],
                'help' => $options['is_edit'] ? 'Laisser vide pour ne pas changer le mot de passe.' : null,
                'constraints' => $options['is_edit'] ? [] : [
                    new NotBlank(),
                    new Length(min: 8),
                ],
            ])
            ->add('submit', SubmitType::class, ['label' => 'Enregistrer'])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'is_edit' => false,
        ]);
        $resolver->setAllowedTypes('is_edit', 'bool');
    }
}
