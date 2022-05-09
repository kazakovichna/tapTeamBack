<?php

namespace App\Admin;

use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Form\FormMapper;

final class AuthorAdmin extends AbstractAdmin
{
    protected function configureFormFields(FormMapper $form): void
    {
        $form->add('authorName', TextType::class);
        $form->add('bookCount', IntegerType::class);
    }

    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        $filter->add('authorName');
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list->add('authorName', TextType::class);
        $list->add('bookCount', IntegerType::class);
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
        $show->add('authorName');
    }
}