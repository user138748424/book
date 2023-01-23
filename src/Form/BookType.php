<?php

namespace App\Form;

use App\Entity\Book;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;

class BookType extends AbstractType
{
    public const LIKES = 'likes';
    public const DIS_LIKES = 'dislikes';

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name')
            ->add('releaseDate', null, [
                'widget' => 'single_text'
            ])
            ->add('catalogEntryDate', null, [
                'widget' => 'single_text'
            ])
            ->add('rating')
            ->add('likes', HiddenType::class, [
                'attr' => [
                    'class' => self::LIKES
                ]
            ])
            ->add('dislikes', HiddenType::class, [
                'attr' => [
                    'class' => self::DIS_LIKES
                ]
            ])
            ->add('genre')
            ->add('author')
            ->add('avatar', FileType::class, ['required' => false, 'data_class'=> null])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Book::class,
        ]);
    }
}
