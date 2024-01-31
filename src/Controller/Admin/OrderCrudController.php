<?php

namespace App\Controller\Admin;

use App\Entity\Order;
use Doctrine\ORM\QueryBuilder;
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
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Faker\Core\Number;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class OrderCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Order::class;
    }

    public function configureFields(string $pageName): iterable
    {

        return [
            IdField::new('id')->hideOnForm(),
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
                ->setCrudController(UserCrudController::class),
                // ->setQueryBuilder(fn (QueryBuilder $qb) => $qb
                //     ->andWhere('entity.roles = "ROLE_EMPLOYEE"')
                // ),
            DateTimeField::new('deposit'),
            DateTimeField::new('pickUp'),
            NumberField::new('totalPrice')->onlyOnDetail(),
            DateTimeField::new('payment')->onlyOnDetail()
        ];
    }
    
    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
        ;
    }
}
