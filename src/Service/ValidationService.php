<?php

namespace App\Service;

use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class ValidationService
{
    private $validator;
    private $flashBag;

    public function __construct(
        ValidatorInterface $validator,
        RequestStack $requestStack
    ) {
        $this->validator = $validator;
        $this->flashBag = $requestStack->getSession()->getFlashBag();
    }

    /**
     * Validate an entity and add flash messages for errors
     * 
     * @param object $entity The entity to validate
     * @param array $groups Validation groups to use
     * @return bool True if validation passed, false otherwise
     */
    public function validate($entity, array $groups = null): bool
    {
        $errors = $this->validator->validate($entity, null, $groups);
        
        if (count($errors) > 0) {
            foreach ($errors as $error) {
                $this->flashBag->add(
                    'error',
                    sprintf('%s: %s', $error->getPropertyPath(), $error->getMessage())
                );
            }
            return false;
        }
        
        return true;
    }

    /**
     * Validate a form and add flash messages for errors
     * 
     * @param \Symfony\Component\Form\FormInterface $form
     * @return bool True if validation passed, false otherwise
     */
    public function validateForm($form): bool
    {
        if (!$form->isValid()) {
            foreach ($form->getErrors(true) as $error) {
                $fieldName = $error->getOrigin() ? $error->getOrigin()->getName() : 'General';
                $this->flashBag->add(
                    'error',
                    sprintf('%s: %s', $fieldName, $error->getMessage())
                );
            }
            return false;
        }
        
        return true;
    }

    /**
     * Custom validation for material (matériel) entities
     * 
     * @param object $materiel
     * @return bool
     */
    public function validateMateriel($materiel): bool
    {
        $isValid = true;
        
        // Check if type is not empty and has minimum length
        if (empty($materiel->getType()) || strlen($materiel->getType()) < 2) {
            $this->flashBag->add('error', 'Le type du matériel doit contenir au moins 2 caractères');
            $isValid = false;
        }
        
        // Check if type sport is set
        if (empty($materiel->getTypeSport())) {
            $this->flashBag->add('error', 'Le type de sport est obligatoire');
            $isValid = false;
        }
        
        // Check if prix is valid
        if ($materiel->getPrix() !== null && $materiel->getPrix() < 0) {
            $this->flashBag->add('error', 'Le prix doit être supérieur ou égal à 0');
            $isValid = false;
        }
        
        return $isValid;
    }
    
    /**
     * Custom validation for loan (emprunt) entities
     * 
     * @param object $emprunt
     * @return bool
     */
    public function validateEmprunt($emprunt): bool
    {
        $isValid = true;
        
        // Check if materiel is set
        if (empty($emprunt->getMateriel())) {
            $this->flashBag->add('error', 'Le matériel est obligatoire');
            $isValid = false;
        }
        
        // Check if user is set
        if (empty($emprunt->getUser())) {
            $this->flashBag->add('error', 'L\'utilisateur est obligatoire');
            $isValid = false;
        }
        
        // Check if dateEmprunt is set
        if (empty($emprunt->getDateEmprunt())) {
            $this->flashBag->add('error', 'La date d\'emprunt est obligatoire');
            $isValid = false;
        }
        
        // Check date logic (dateRetourPrevu after dateEmprunt)
        if ($emprunt->getDateEmprunt() && $emprunt->getDateRetourPrevu() 
            && $emprunt->getDateRetourPrevu() < $emprunt->getDateEmprunt()) {
            $this->flashBag->add('error', 'La date de retour prévue doit être postérieure à la date d\'emprunt');
            $isValid = false;
        }
        
        return $isValid;
    }
    
    /**
     * Custom validation for maintenance entities
     * 
     * @param object $maintenance
     * @return bool
     */
    public function validateMaintenance($maintenance): bool
    {
        $isValid = true;
        
        // Check if materiel is set
        if (empty($maintenance->getMateriel())) {
            $this->flashBag->add('error', 'Le matériel est obligatoire');
            $isValid = false;
        }
        
        // Check if type is set
        if (empty($maintenance->getType())) {
            $this->flashBag->add('error', 'Le type de maintenance est obligatoire');
            $isValid = false;
        }
        
        // Check if datemaintenance is set
        if (empty($maintenance->getDatemaintenance())) {
            $this->flashBag->add('error', 'La date de maintenance est obligatoire');
            $isValid = false;
        }
        
        // Check if cout is valid
        if ($maintenance->getCout() < 0) {
            $this->flashBag->add('error', 'Le coût doit être un nombre positif');
            $isValid = false;
        }
        
        // Check date logic (datefin after datemaintenance)
        if ($maintenance->getDatemaintenance() && $maintenance->getDatefin() 
            && $maintenance->getDatefin() < $maintenance->getDatemaintenance()) {
            $this->flashBag->add('error', 'La date de fin doit être postérieure à la date de maintenance');
            $isValid = false;
        }
        
        return $isValid;
    }
} 