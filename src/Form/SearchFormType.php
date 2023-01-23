<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use App\Repository\GenreRepository;
use App\Repository\AuthorRepository;

/**
 * Class SearchFormType
 * @package App\Form
 */
class SearchFormType extends AbstractType
{
    /**
     * @var GenreRepository
     */
    private $genreRepository;

    /**
     * @var AuthorRepository
     */
    private $authorRepository;

    public function __construct(
        GenreRepository $genreRepository,
        AuthorRepository $authorRepository
    ) {
        $this->genreRepository = $genreRepository;
        $this->authorRepository = $authorRepository;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $genres = $this->genreRepository->findAll();

        $genresAliases = [];
        foreach ($genres as $genre) {
            $genresAliases[$genre->getName()] = $genre->getId();
        }

        $authors = $this->authorRepository->findAll();

        $authorsAliases = [];
        foreach ($authors as $author) {
            $authorsAliases[$author->getName()] = $author->getId();
        }

        $builder
            ->add('genre', ChoiceType::class, array(
                'choices' => $genresAliases,
                'required' => false
            ))
            ->add('author', ChoiceType::class, array(
                'choices' => $authorsAliases,
                'required' => false
            ))
            ->add('date_start', TextType::class, ['required' => false])
            ->add('date_end', TextType::class, ['required' => false])
            ->add('submit', SubmitType::class);
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
