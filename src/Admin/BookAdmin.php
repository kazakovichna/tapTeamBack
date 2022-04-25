<?php

namespace App\Admin;

use App\Entity\Author;
use App\Form\AuthorType;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

final class BookAdmin extends AbstractAdmin
{
    protected function configureFormFields(FormMapper $formMapper): void
    {
        $formMapper->add('bookName', TextType::class);
        $formMapper->add('bookDescription', TextType::class);
        $formMapper->add('bookYear', TextType::class);
        $formMapper->add('authorList', CollectionType::class, array(
          'entry_type' => AuthorType::class,
          'entry_options' => ['label' => false],
          'allow_add' => true,
          'by_reference' => false
        ));
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper): void
    {
        $datagridMapper->add('bookName');
    }

    protected function configureListFields(ListMapper $listMapper): void
    {
        $listMapper->add('bookName', TextType::class);
        $listMapper->add('bookDescription', TextType::class);
        $listMapper->add('bookYear', TextType::class);
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
        $show->add('bookName');
    }
}