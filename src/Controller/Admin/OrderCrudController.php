<?php

namespace App\Controller\Admin;

use App\Entity\Order;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;

class OrderCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Order::class;
    }

    public function configureFields(string $pageName): iterable
    {

        return [
            IdField::new('id')->setFormTypeOption('disabled','disabled'),
            ChoiceField::new('status')
                ->renderAsBadges([
                    'waiting' => 'danger',
                    'preparing' => 'warning',
                    'ready' => 'primary',
                    'collected' => 'success',
                ])
                ->setChoices([
                    'En attente' => 'waiting',
                    'En cours' => 'preparing',
                    'Prête' => 'ready',
                    'Récupérée' => 'collected'
                ]),
            ArrayField::new('content')->hideOnForm(),
            TextEditorField::new('message')->hideOnForm(),
            AssociationField::new('client')->hideOnForm()
                ->setFormTypeOption('by_reference', false)
                ->setCrudController(UserCrudController::class),
            AssociationField::new('employee')
                ->autocomplete()
                ->setCrudController(UserCrudController::class),
                // ->setQueryBuilder(fn (QueryBuilder $qb) => $qb
                //     ->select('entity')
                //     ->where('"ROLE_EMPLOYEE" IN(entity.roles)')
                // ),
            DateTimeField::new('deposit'),
            DateTimeField::new('pickUp'),
            NumberField::new('totalPrice')->onlyOnDetail()
                ->formatValue(function ($value) {
                    $euroValue = $value / 100;
                    return number_format($euroValue, 2, ',', ' ') . ' €';
                }),
            DateTimeField::new('payment')->onlyOnDetail()
        ];
    }
    
    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)            
            ->setPermission(Action::NEW, 'ROLE_ADMIN')
            ->setPermission(Action::DELETE, 'ROLE_ADMIN')
            ->setPermission(Action::EDIT, 'ROLE_EMPLOYEE')
        ;
    }
}
