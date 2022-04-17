<?php

namespace App\Admin;

use App\Entity\Author;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

final class BookAdmin extends AbstractAdmin
{
    protected function configureFormFields(FormMapper $formMapper): void
    {
        $formMapper->add('book_name', TextType::class);
        $formMapper->add('book_descr', TextType::class);
        $formMapper->add('book_year', TextType::class);
        $formMapper->add('authorList', EntityType::class, [
            'class' => Author::class,
            'choice_label' => 'author_name'
        ]);
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper): void
    {
        $datagridMapper->add('book_name');
    }

    protected function configureListFields(ListMapper $listMapper): void
    {
        $listMapper->addIdentifier('book_name');
    }

    protected function configureShowFields(ShowMapper $show): void
    {
        $show->add('book_name');
    }
}