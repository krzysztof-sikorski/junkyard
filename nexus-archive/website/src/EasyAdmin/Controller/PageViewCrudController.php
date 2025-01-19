<?php

declare(strict_types=1);

namespace App\EasyAdmin\Controller;

use App\Doctrine\Entity\PageView;
use App\EasyAdmin\Field\JsonField;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\ChoiceFilter;
use Symfony\Component\HttpFoundation\Request;

use function json_encode;

final class PageViewCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return PageView::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return parent::configureCrud(crud: $crud)
            ->setSearchFields(fieldNames: ['id', 'url'])
            ->setDefaultSort(sortFieldsAndOrder: ['createdAt' => 'DESC']);
    }

    public function configureFields(string $pageName): iterable
    {
        $jsonFormatter = static function (mixed $value): string {
            return json_encode(value: $value);
        };

        yield IdField::new(propertyName: 'id')->setMaxLength(length: -1)->setDisabled(disabled: true);
        yield DateTimeField::new(propertyName: 'createdAt');
        yield AssociationField::new(propertyName: 'owner')->onlyOnDetail();
        yield DateTimeField::new(propertyName: 'requestStartedAt')->onlyOnDetail();
        yield DateTimeField::new(propertyName: 'responseCompletedAt')->onlyOnDetail();
        yield TextField::new(propertyName: 'method');
        yield TextField::new(propertyName: 'url')->setMaxLength(length: 255);
        yield JsonField::new(propertyName: 'formData')->formatValue(callable: $jsonFormatter);
        yield TextEditorField::new(propertyName: 'responseBody')->onlyOnDetail();
        yield DateTimeField::new(propertyName: 'parsedAt');
        yield JsonField::new(propertyName: 'parserErrors')->onlyOnDetail()->formatValue(callable: $jsonFormatter);
    }

    public function configureFilters(Filters $filters): Filters
    {
        $methodChoices = [Request::METHOD_GET => Request::METHOD_GET, Request::METHOD_POST => Request::METHOD_POST];
        $methodFilter = ChoiceFilter::new(propertyName: 'method')->setChoices(choices: $methodChoices);

        return parent::configureFilters(filters: $filters)
            ->add(propertyNameOrFilter: 'id')
            ->add(propertyNameOrFilter: 'createdAt')
            ->add(propertyNameOrFilter: 'owner')
            ->add(propertyNameOrFilter: $methodFilter)
            ->add(propertyNameOrFilter: 'url')
            ->add(propertyNameOrFilter: 'parsedAt');
    }

    public function configureActions(Actions $actions): Actions
    {
        return parent::configureActions(actions: $actions)
            ->add(pageName: Crud::PAGE_INDEX, actionNameOrObject: Action::DETAIL)
            ->disable(
                Action::BATCH_DELETE,
                Action::DELETE,
                Action::EDIT,
                Action::NEW,
                Action::SAVE_AND_ADD_ANOTHER,
                Action::SAVE_AND_CONTINUE,
                Action::SAVE_AND_RETURN
            );
    }
}
