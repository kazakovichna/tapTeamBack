<?php

namespace App\Admin;

use App\Entity\Book;
use App\Form\BookType;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Form\FormMapper;

final class AuthorAdmin extends AbstractAdmin
{
    protected function configureFormFields(FormMapper $form): void
    {
        $form->add('authorName', TextType::class);
        $form->add('bookCount', IntegerType::class);
        $form->add('booksList', CollectionType::class, array(
            'entry_type' => BookType::class,
            'entry_options' => ['label' => false],
            'allow_add' => true,
            'by_reference' => false
        ));
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper): void
    {
        $datagridMapper->add('authorName');
    }

    protected function configureListFields(ListMapper $listMapper): void
    {
        $listMapper->add('authorName', TextType::class);
        $listMapper->add('bookCount', IntegerType::class);
        $listMapper->add(listMapper::NAME_ACTIONS, null, [
            'actions' => [
                'show' => [],
                'edit' => [],
                'delete' => []
            ]
        ]);
    }

    protected function configureShowFields(ShowMapper $show): void
    {
        $show->add('authorName');
    }
}