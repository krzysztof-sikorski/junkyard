<?php

declare(strict_types=1);

namespace App\EasyAdmin\Controller;

use App\Doctrine\Entity\UserAccessToken;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

final class UserAccessTokenCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return UserAccessToken::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return parent::configureCrud(crud: $crud)
            ->setSearchFields(fieldNames: ['id', 'value'])
            ->setDefaultSort(sortFieldsAndOrder: ['createdAt' => 'DESC']);
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new(propertyName: 'id')->setMaxLength(length: -1)->setDisabled(disabled: true);
        yield TextField::new(propertyName: 'value')->setMaxLength(length: 40);
        yield DateTimeField::new(propertyName: 'createdAt');
        yield DateTimeField::new(propertyName: 'validUntil');
        yield AssociationField::new(propertyName: 'owner')->autocomplete();
    }

    public function configureFilters(Filters $filters): Filters
    {
        return parent::configureFilters(filters: $filters)
            ->add(propertyNameOrFilter: 'id')
            ->add(propertyNameOrFilter: 'value')
            ->add(propertyNameOrFilter: 'createdAt')
            ->add(propertyNameOrFilter: 'validUntil')
            ->add(propertyNameOrFilter: 'owner');
    }
}
