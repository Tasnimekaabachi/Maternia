<?php

namespace App\Form\Model;

use Symfony\Component\Validator\Constraints as Assert;

class AppointmentData
{
    #[Assert\NotBlank(message: 'Le nom est obligatoire.')]
    #[Assert\Length(max: 255, maxMessage: 'Le nom ne peut pas dépasser 255 caractères.')]
    public ?string $nom = null;

    #[Assert\NotBlank(message: 'Le prénom est obligatoire.')]
    #[Assert\Length(max: 255, maxMessage: 'Le prénom ne peut pas dépasser 255 caractères.')]
    public ?string $prenom = null;

    #[Assert\NotBlank(message: 'L’email est obligatoire.')]
    #[Assert\Email(message: 'L’email n’est pas valide.')]
    #[Assert\Length(max: 255)]
    public ?string $email = null;

    #[Assert\NotBlank(message: 'Le téléphone est obligatoire.')]
    #[Assert\Length(max: 30, maxMessage: 'Le téléphone ne peut pas dépasser 30 caractères.')]
    public ?string $telephone = null;

    #[Assert\Length(max: 20)]
    public ?string $dateSouhaitee = null;

    #[Assert\Choice(
        choices: ['suivi_grossesse', 'consultation_postnatale', 'allaitement', 'bilan', 'autre'],
        message: 'Type de consultation invalide.'
    )]
    public ?string $typeConsultation = null;

    #[Assert\Length(max: 2000, maxMessage: 'Le message ne peut pas dépasser 2000 caractères.')]
    public ?string $message = null;
}

