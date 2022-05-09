<?php

namespace App\Admin;

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
    protected function configureFormFields(FormMapper $form): void
    {
        $form->add('bookName', TextType::class);
        $form->add('bookDescription', TextType::class);
        $form->add('bookYear', TextType::class);
        $form->add('authorList', CollectionType::class, array(
          'entry_type' => AuthorType::class,
          'entry_options' => ['label' => false],
          'allow_add' => true,
          'by_reference' => false
        ));
    }

    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        $filter->add('bookName');
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list->add('bookName', TextType::class);
        $list->add('bookDescription', TextType::class);
        $list->add('bookYear', TextType::class);
        $list->add(listMapper::NAME_ACTIONS, null, [
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
        $show->add('bookDescription');
        $show->add('bookYear');
    }
}