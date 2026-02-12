# Bilan complet – Entités et relations (Maternia)

## Vue d’ensemble

Le projet comporte **3 entités** principales, liées entre elles comme suit :

```
┌─────────────────┐         ┌──────────────────────┐         ┌─────────────────────┐
│  Consultation   │  1    N │  ConsultationCreneau  │  1    1 │  ReservationClient   │
│                 │─────────│                        │─────────│                      │
│ (type de RDV)   │         │ (créneau horaire)      │         │ (patient qui réserve)│
└─────────────────┘         └──────────────────────┘         └─────────────────────┘
```

- **Consultation** → type de consultation (ex. Échographie, Pédiatre). Une consultation a **plusieurs créneaux**.
- **ConsultationCreneau** → un créneau précis (médecin, date/heure). Un créneau appartient à **une** consultation et peut avoir **au plus une** réservation.
- **ReservationClient** → données du patient qui a réservé **un** créneau. Une réservation est liée à **un seul** créneau.

---

## 1. Consultation

**Rôle :** Définit un **type de consultation** (catégorie, public cible, ordre d’affichage). C’est ce que l’utilisateur choisit en premier (ex. « Échographie », « Suivi grossesse »).

| Attribut           | Type        | BDD / Relation | Description |
|--------------------|------------|----------------|-------------|
| id                 | int        | PK             | Identifiant |
| categorie          | string(100)| NOT NULL       | Nom de la consultation (ex. « Échographie ») |
| description        | text       | nullable       | Texte de présentation |
| pour               | string(50) | NOT NULL       | Public : MAMAN, BEBE, LES_DEUX |
| image              | string(255)| nullable       | Chemin image |
| icon               | string(255)| nullable       | Classe icône (ex. fas fa-baby) |
| statut             | bool       | NOT NULL       | Actif / inactif |
| ordreAffichage     | int        | nullable       | Ordre dans les listes |
| createdAt          | datetime   | nullable       | Date de création |
| updatedAt          | datetime   | nullable       | Dernière modification |
| **consultationCreneaus** | Collection | **OneToMany** → ConsultationCreneau | Tous les créneaux de cette consultation |

**Relation :**

- **Consultation** 1 ——→ N **ConsultationCreneau** (`consultationCreneaus` / `mappedBy: 'consultation'`)

---

## 2. ConsultationCreneau

**Rôle :** Représente **un créneau horaire** concret : quel médecin, quel jour, quelle heure, pour quelle consultation. C’est l’élément que l’on réserve.

| Attribut            | Type        | BDD / Relation | Description |
|---------------------|------------|----------------|-------------|
| id                  | int        | PK             | Identifiant |
| **consultation**    | Consultation | ManyToOne, NOT NULL | Type de consultation |
| nomMedecin          | string(100)| NOT NULL       | Nom du médecin |
| photoMedecin        | string(255)| nullable       | Nom du fichier photo (ex. dans uploads/medecins/) |
| descriptionMedecin  | text       | nullable       | Présentation du médecin |
| specialiteMedecin   | string(100)| nullable       | Spécialité |
| dateDebut           | datetime   | NOT NULL       | Début du créneau (date + heure) |
| dateFin             | datetime   | NOT NULL       | Fin du créneau |
| jour                | date       | nullable       | Jour seul (dérivé / formulaire) |
| heureDebut          | time       | nullable       | Heure de début (dérivé / formulaire) |
| heureFin            | time       | nullable       | Heure de fin (dérivé / formulaire) |
| statutReservation   | string(20) | NOT NULL       | DISPONIBLE, RESERVE, ANNULE, CONFIRME |
| dureeMinutes        | int        | nullable, défaut 30 | Durée en minutes |
| nombrePlaces        | int        | nullable, défaut 1  | Capacité du créneau |
| createdAt           | datetime   | nullable       | Création |
| updatedAt           | datetime   | nullable       | Modification |
| **reservation**     | ReservationClient | **OneToOne** (mappedBy) | Réservation associée, si existante |

**Relations :**

- **ConsultationCreneau** N ——→ 1 **Consultation** (`consultation`, `inversedBy: 'consultationCreneaus'`)
- **ConsultationCreneau** 1 ——→ 1 **ReservationClient** (`reservation`, `mappedBy: 'consultationCreneau'`, cascade persist/remove)

**Note :** `dateDebut` / `dateFin` sont synchronisés avec `jour` + `heureDebut` / `heureFin` dans l’entité (méthode `syncDates()`).

---

## 3. ReservationClient

**Rôle :** Enregistre **qui** a réservé **quel créneau** : identité du patient, contact, type (maman/bébé), infos grossesse/bébé, statut et référence de la réservation.

| Attribut              | Type        | BDD / Relation | Description |
|-----------------------|------------|----------------|-------------|
| id                    | int        | PK             | Identifiant |
| **consultationCreneau** | ConsultationCreneau | OneToOne, NOT NULL | Créneau réservé |
| nomClient             | string(100)| NOT NULL       | Nom du patient |
| prenomClient          | string(100)| NOT NULL       | Prénom |
| emailClient           | string(100)| NOT NULL       | Email |
| telephoneClient       | string(20) | NOT NULL       | Téléphone (8 chiffres) |
| typePatient           | string(20) | NOT NULL       | Maman / Bébé / etc. |
| moisGrossesse         | int        | nullable       | Mois de grossesse si pertinent |
| dateNaissanceBebe    | date       | nullable       | Date de naissance du bébé si pertinent |
| statutReservation     | string(50) | NOT NULL       | Statut de la réservation |
| reference             | string(50) | NOT NULL       | Numéro / référence de réservation |
| dateReservation       | datetime   | NOT NULL       | Date et heure de la réservation |
| notes                 | text       | nullable       | Notes internes |
| createdAt             | datetime_immutable | NOT NULL | Création |
| updatedAt             | datetime_immutable | nullable | Modification |

**Relation :**

- **ReservationClient** 1 ——→ 1 **ConsultationCreneau** (`consultationCreneau`, `inversedBy: 'reservation'`)

---

## Schéma des relations (résumé)

| Entité               | Relation        | Vers                  | Cardinalité | Propriété côté entité |
|----------------------|-----------------|------------------------|------------|------------------------|
| Consultation         | OneToMany       | ConsultationCreneau   | 1 → N      | `consultationCreneaus` |
| ConsultationCreneau   | ManyToOne       | Consultation          | N → 1      | `consultation`         |
| ConsultationCreneau   | OneToOne (inverse) | ReservationClient  | 1 → 0 ou 1 | `reservation`          |
| ReservationClient    | OneToOne (owner)| ConsultationCreneau   | 1 → 1      | `consultationCreneau`  |

- **Owner** de la relation OneToOne : `ReservationClient` (porte la clé étrangère `consultation_creneau_id`).
- **Cascade :** sur la relation ConsultationCreneau ↔ ReservationClient : `persist`, `remove` (côté ConsultationCreneau).

---

## Parcours typiques

1. **Afficher les types de consultation**  
   → Liste des `Consultation` (filtrée par `statut`, tri par `ordreAffichage`).

2. **Afficher les créneaux d’une consultation**  
   → `Consultation` → `getConsultationCreneaus()` ou requête sur `ConsultationCreneau` avec `consultation = :id` et `dateDebut > now`.

3. **Afficher les médecins pour une consultation**  
   → Créneaux de cette consultation, groupés par `nomMedecin` (avec photo, spécialité, etc.).

4. **Réserver un créneau**  
   → Création d’un `ReservationClient` lié au `ConsultationCreneau` choisi ; mise à jour du `statutReservation` du créneau (ex. RESERVE).

5. **Voir qui a réservé un créneau**  
   → `ConsultationCreneau` → `getReservation()` → `ReservationClient` (nom, prénom, email, téléphone, etc.).

---

## Fichiers concernés

- **Entités :** `src/Entity/Consultation.php`, `ConsultationCreneau.php`, `ReservationClient.php`
- **Repositories :** `ConsultationRepository`, `ConsultationCreneauRepository`, `ReservationClientRepository`
- **Tables :** `consultation`, `consultation_creneau`, `reservation_client`

Ce document reflète l’état des entités et relations du projet Maternia à la date du bilan.
