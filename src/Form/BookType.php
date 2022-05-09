<?php

namespace App\Form;

use App\Entity\Author;
use App\Entity\Book;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BookType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('bookName', TextType::class)
            ->add('bookDescription', TextType::class)
            ->add('bookYear', TextType::class)
            ->add('authorList', CollectionType::class, array(
                'entry_type' => AuthorType::class,
                'entry_options' => ['label' => false],
                'allow_add' => true,
                'by_reference' => false
            ));
    }
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Book::class
        ]);
    }
}