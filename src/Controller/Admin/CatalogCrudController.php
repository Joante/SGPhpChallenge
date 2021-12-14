<?php

namespace App\Controller\Admin;

use App\Controller\Admin\Field\FileField;
use App\Entity\Catalog;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class CatalogCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Catalog::class;
    }

    public function createEntity(string $entityFqcn)
    {
        $catalog = new Catalog();
        $catalog->setCreatedAt(new \DateTimeImmutable);

        return $catalog;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IntegerField::new('id')->hideOnForm(),
            TextField::new('filePath')->hideOnForm(),
            DateTimeField::new('createdAt')->hideOnForm(),
            DateTimeField::new('updatedAt')->hideOnForm(),
            TextField::new('state')->hideOnForm(),
            FileField::new('file')->onlyOnForms()->setFormTypeOptions([
                'required' => true,
            ]),
        ];
    }

    public function configureCrud(Crud $crud): Crud
    {
        
        return $crud
            //->overrideTemplate('crud/new', 'bundles/EasyAdminBundle/crud/new.html.twig')
            // the max number of entities to display per page
            ->setPaginatorPageSize(30)
            // the number of pages to display on each side of the current page
            // e.g. if num pages = 35, current page = 7 and you set ->setPaginatorRangeSize(4)
            // the paginator displays: [Previous]  1 ... 3  4  5  6  [7]  8  9  10  11 ... 35  [Next]
            // set this number to 0 to display a simple "< Previous | Next >" pager
            ->setPaginatorRangeSize(4)
        ;
    }
}
