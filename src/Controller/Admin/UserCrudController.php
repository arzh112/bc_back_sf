<?php

namespace App\Controller\Admin;

use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class UserCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return User::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            // Le champ Id est masqué sur les formulaires
            IdField::new('id')->hideOnForm(),
            TextField::new('email'),
            TextField::new('firstname'),
            TextField::new('lastname'),
            ChoiceField::new('roles')
                ->allowMultipleChoices()
                ->renderAsBadges([
                    'ROLE_ADMIN' => 'success',
                    'ROLE_EMPLOYEE' => 'primary',
                    'ROLE_USER' => 'warning',
                ])
                ->setChoices([
                    'Admin' => 'ROLE_ADMIN',
                    'Employee' => 'ROLE_EMPLOYEE',
                    'User' => 'ROLE_USER'
                ]),
            // Le champ mot de passe n'est affiché que sur les formulaires de création ou d'édition
            TextField::new('password')->onlyOnForms(),
            // le champ date de naissance est masqué sur l'index
            DateTimeField::new('birthdate')->hideOnIndex(),
            TextField::new('gender')->onlyOnDetail(),
            TextEditorField::new('address')->onlyOnForms(),
        ];
    }
    
    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL) // Ajoute l'action voir le détail sur l'index des utilisateurs
            ->setPermission(Action::NEW, 'ROLE_ADMIN') // N'autorise l'accès à la page de création qu'aux utilisateurs admin
            ->setPermission(Action::DELETE, 'ROLE_ADMIN')
            ->setPermission(Action::EDIT, 'ROLE_ADMIN')
        ;
    }
}
