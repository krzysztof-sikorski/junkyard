<?php

declare(strict_types=1);

namespace App\EasyAdmin\Controller;

use App\Doctrine\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

final class UserCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return User::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return parent::configureCrud(crud: $crud)
            ->setSearchFields(fieldNames: ['id', 'username'])
            ->setDefaultSort(sortFieldsAndOrder: ['createdAt' => 'DESC']);
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new(propertyName: 'id')->setMaxLength(length: -1)->setDisabled();
        yield TextField::new(propertyName: 'username');
        yield DateTimeField::new(propertyName: 'createdAt');
        yield BooleanField::new(propertyName: 'enabled');
    }

    public function configureFilters(Filters $filters): Filters
    {
        return parent::configureFilters(filters: $filters)
            ->add(propertyNameOrFilter: 'id')
            ->add(propertyNameOrFilter: 'username')
            ->add(propertyNameOrFilter: 'createdAt')
            ->add(propertyNameOrFilter: 'enabled');
    }
}
