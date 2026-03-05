-- ============================================
-- TEST DATA POPULATION SCRIPT FOR MATERNIA
-- ============================================

-- 1. Clear existing data (optional - comment out if you want to keep existing data)
-- ============================================
-- SET FOREIGN_KEY_CHECKS = 0;
-- TRUNCATE TABLE `attendance`;
-- TRUNCATE TABLE `commande_produit`;
-- TRUNCATE TABLE `commande`;
-- TRUNCATE TABLE `consultation_creneau`;
-- TRUNCATE TABLE `consultation`;
-- TRUNCATE TABLE `demande_baby_sitter`;
-- TRUNCATE TABLE `event_requirement`;
-- TRUNCATE TABLE `event`;
-- TRUNCATE TABLE `event_cat`;
-- TRUNCATE TABLE `grosesse`;
-- TRUNCATE TABLE `maman`;
-- TRUNCATE TABLE `offre_baby_sitter`;
-- TRUNCATE TABLE `produit`;
-- TRUNCATE TABLE `promo_code`;
-- TRUNCATE TABLE `requirement`;
-- TRUNCATE TABLE `reservation_client`;
-- TRUNCATE TABLE `user` WHERE id > 7; -- Keep existing admin users
-- SET FOREIGN_KEY_CHECKS = 1;

-- ============================================
-- 2. ADD MORE USERS (MAMANS)
-- ============================================
INSERT INTO `user` (`email`, `roles`, `password`, `nom`, `prenom`, `type`, `facial_id`) VALUES
('sarah.mansour@gmail.com', '["ROLE_MAMAN"]', '$2y$13$CuzvD2Rkgx3v5ixTYpDqPOvd1PA4juQZiJRKiqUUuzgXKeaK70dvu', 'Mansour', 'Sarah', 'MAMAN', NULL),
('amina.benali@gmail.com', '["ROLE_MAMAN"]', '$2y$13$CuzvD2Rkgx3v5ixTYpDqPOvd1PA4juQZiJRKiqUUuzgXKeaK70dvu', 'Ben Ali', 'Amina', 'MAMAN', NULL),
('nour.haddad@gmail.com', '["ROLE_MAMAN"]', '$2y$13$CuzvD2Rkgx3v5ixTYpDqPOvd1PA4juQZiJRKiqUUuzgXKeaK70dvu', 'Haddad', 'Nour', 'MAMAN', NULL),
('fatma.trabelsi@gmail.com', '["ROLE_MAMAN"]', '$2y$13$CuzvD2Rkgx3v5ixTYpDqPOvd1PA4juQZiJRKiqUUuzgXKeaK70dvu', 'Trabelsi', 'Fatma', 'MAMAN', NULL),
('imen.gharbi@gmail.com', '["ROLE_MAMAN"]', '$2y$13$CuzvD2Rkgx3v5ixTYpDqPOvd1PA4juQZiJRKiqUUuzgXKeaK70dvu', 'Gharbi', 'Imen', 'MAMAN', NULL);

-- ============================================
-- 3. ADD MAMAN PROFILES
-- ============================================
INSERT INTO `maman` (`numero_urgence`, `email`, `date_naissance`, `groupe_sanguin`, `allergies`, `antecedents_medicaux`, `poids`, `taille`, `maladies_chroniques`, `medicaments_actuels`, `fumeur`, `consommation_alcool`, `niveau_activite_physique`, `habitudes_alimentaires`, `date_creation`, `date_mise_ajour`) VALUES
('98765432', 'sarah.mansour@gmail.com', '1995-03-15', 'A+', 'Pénicilline, acariens', 'Aucun', 68, 165, NULL, 'Vitamine D', 0, 0, 'Modéré', 'équilibrée', NOW(), NOW()),
('97654321', 'amina.benali@gmail.com', '1992-07-22', 'O+', 'Latex', 'Césarienne précédente', 72, 168, 'Hypothyroïdie', 'Levothyrox', 0, 0, 'Léger', 'végétarienne', NOW(), NOW()),
('96543210', 'nour.haddad@gmail.com', '1990-11-08', 'B-', 'Aucune', 'Aucun', 65, 162, NULL, NULL, 0, 0, 'Actif', 'sans gluten', NOW(), NOW()),
('95432109', 'fatma.trabelsi@gmail.com', '1988-09-30', 'AB+', 'Fruits de mer', 'Hypertension gestationnelle', 75, 170, 'Pré-éclampsie', 'Aspirine', 0, 0, 'Sédentaire', 'méditerranéenne', NOW(), NOW()),
('94321098', 'imen.gharbi@gmail.com', '1993-12-05', 'A-', 'Pollen, chat', 'Aucun', 70, 166, NULL, 'Fer', 0, 0, 'Modéré', 'équilibrée', NOW(), NOW());

-- ============================================
-- 4. ADD GROSSESSE RECORDS
-- ============================================
INSERT INTO `grosesse` (`connait_ddr`, `date_dernieres_regles`, `statut_grossesse`, `type_grossesse`, `poids_actuel`, `nausee`, `vomissement`, `saignement`, `fievre`, `douleur_abdominale`, `fatigue`, `vertiges`, `maman_id`, `date_creation`, `date_mise_ajour`) VALUES
(1, '2025-12-15', 'enCours', 'simple', 72, 1, 0, 0, 0, 0, 1, 0, 2, NOW(), NOW()), -- Sarah, 12 semaines
(1, '2025-10-20', 'enCours', 'simple', 75, 0, 0, 0, 0, 0, 1, 0, 3, NOW(), NOW()), -- Amina, 20 semaines
(1, '2025-08-05', 'enCours', 'multiple', 80, 0, 0, 0, 0, 1, 1, 1, 4, NOW(), NOW()), -- Nour, 30 semaines (jumeaux)
(1, '2025-06-10', 'terminee', 'simple', NULL, 0, 0, 0, 0, 0, 0, 0, 5, NOW(), NOW()), -- Fatma (déjà accouchée)
(1, '2026-01-25', 'enCours', 'simple', 71, 1, 1, 0, 0, 0, 1, 1, 6, NOW(), NOW()); -- Imen, 6 semaines

-- Update grossesse with baby info for terminee
UPDATE `grosesse` SET 
    `date_accouchement_reelle` = '2026-03-01',
    `nombre_bebes` = 1,
    `nom_bebe` = 'Lina',
    `sexe_bebe` = 'F',
    `poids_naissance` = 3.2,
    `taille_naissance` = 48,
    `etat_naissance` = 'Excellent'
WHERE `id` = 4;

-- ============================================
-- 5. ADD EVENT CATEGORIES (more)
-- ============================================
INSERT INTO `event_cat` (`name`, `description`) VALUES
('Yoga prénatal', 'Séances de yoga adaptées aux femmes enceintes'),
('Cours d\'allaitement', 'Apprenez les techniques d\'allaitement'),
('Portage bébé', 'Ateliers pour apprendre à porter son bébé'),
('Massage bébé', 'Techniques de massage pour nourrissons'),
('Café des parents', 'Rencontres pour échanger entre parents');

-- ============================================
-- 6. ADD MORE EVENTS
-- ============================================
INSERT INTO `event` (`title`, `description`, `start_at`, `end_at`, `location`, `image`, `is_weekly`, `capacity`, `is_outdoor`, `event_cat_id`, `creator_id`) VALUES
('Yoga prénatal - Session matin', 'Séance de yoga doux pour futures mamans. Apportez votre tapis.', '2026-03-15 09:00:00', '2026-03-15 10:30:00', 'Espace Zen, Centre Urbain Nord, Tunis', 'yoga.jpg', 0, 15, 0, 16, 1),
('Cours d\'allaitement', 'Apprenez les bases de l\'allaitement avec une consultante certifiée.', '2026-03-18 14:00:00', '2026-03-18 16:00:00', 'Clinique El Manar, Tunis', 'allaitement.jpg', 0, 12, 0, 17, 6),
('Café des mamans', 'Rencontre conviviale pour échanger sur la maternité.', '2026-03-20 10:00:00', '2026-03-20 12:00:00', 'Café Livres, Lafayette, Tunis', 'cafe.jpg', 1, 20, 0, 19, 1),
('Atelier portage', 'Découvrez les différentes écharpes de portage.', '2026-03-22 15:00:00', '2026-03-22 17:00:00', 'Espace Parentalité, Mutuelle-Ville, Tunis', 'portage.jpg', 0, 10, 0, 18, 6),
('Massage bébé', 'Apprenez à masser votre bébé pour favoriser son bien-être.', '2026-03-25 09:30:00', '2026-03-25 11:00:00', 'PMI Centre, Tunis', 'massage.jpg', 1, 12, 0, 19, 1),
('Promenade entre mamans', 'Marche en plein air pour futures et jeunes mamans.', '2026-03-27 09:00:00', '2026-03-27 11:00:00', 'Parc du Belvédère, Tunis', 'promenade.jpg', 0, 30, 1, 20, 6);

-- Weekly events
INSERT INTO `event` (`title`, `description`, `is_weekly`, `day_of_week`, `start_time`, `end_time`, `location`, `image`, `capacity`, `is_outdoor`, `event_cat_id`, `creator_id`) VALUES
('Yoga hebdomadaire', 'Séance de yoga prénatal chaque semaine', 1, 'Monday', '10:00:00', '11:30:00', 'Espace Zen, Centre Urbain Nord, Tunis', 'yoga-weekly.jpg', 15, 0, 16, 1),
('Groupe de parole', 'Groupe d\'échange pour mamans', 1, 'Wednesday', '14:00:00', '16:00:00', 'Espace Parentalité, Mutuelle-Ville, Tunis', 'groupe.jpg', 12, 0, 19, 6);

-- ============================================
-- 7. ADD ATTENDANCES (users attending events)
-- ============================================
INSERT INTO `attendance` (`created_at`, `user_id`, `event_id`) VALUES
(NOW(), 2, 27), -- Sarah attends Yoga prénatal
(NOW(), 3, 27), -- Amina attends Yoga prénatal
(NOW(), 4, 28), -- Nour attends Cours d'allaitement
(NOW(), 5, 29), -- Fatma attends Café des mamans
(NOW(), 6, 29), -- Imen attends Café des mamans
(NOW(), 2, 30), -- Sarah attends Atelier portage
(NOW(), 3, 31), -- Amina attends Massage bébé
(NOW(), 1, 32); -- Admin attends Promenade

-- ============================================
-- 8. ADD MORE PRODUCTS TO MARKETPLACE
-- ============================================
INSERT INTO `produit` (`nom`, `description`, `prix`, `stock`, `categorie`, `image_name`, `poids_kg`, `sku`, `rating_average`, `rating_count`) VALUES
('Tapis de yoga prénatal', 'Tapis antidérapant spécial femmes enceintes', 45.90, 25, 'sport', 'tapis-yoga.jpg', 1.2, 'SPO-YOG-001', 4.8, 12),
('Ceinture de soutien grossesse', 'Ceinture pour soulager le dos pendant la grossesse', 39.90, 18, 'grossesse', 'ceinture.jpg', 0.3, 'GRO-CEI-001', 4.5, 8),
('Huile anti-vergetures bio', 'Huile naturelle pour prévenir les vergetures', 24.90, 45, 'soins', 'huile.jpg', 0.25, 'SOI-HUI-001', 4.7, 24),
('Coussin d\'allaitement XXL', 'Grand coussin confortable pour l\'allaitement', 59.90, 12, 'allaitement', 'coussin-xl.jpg', 1.8, 'ALL-COU-001', 4.9, 16),
('Biberon anti-colique 250ml', 'Lot de 3 biberons avec tétine physiologique', 29.90, 40, 'bebe', 'biberon-3.jpg', 0.5, 'BEB-BIB-001', 4.6, 32),
('Stérilisateur micro-ondes', 'Stérilise jusqu\'à 4 biberons en 5 minutes', 49.90, 15, 'bebe', 'sterilisateur.jpg', 1.5, 'BEB-STE-001', 4.8, 10),
('Echarpe de portage', 'Echarpe extensible pour porter bébé', 69.90, 8, 'portage', 'echarpe.jpg', 0.8, 'POR-ECH-001', 4.9, 22),
('Couches lavables lot 6', 'Lot de 6 couches lavables taille unique', 89.90, 10, 'bebe', 'couches.jpg', 2.5, 'BEB-COU-001', 4.4, 15),
('Thermomètre frontal', 'Thermomètre infrarouge sans contact', 34.90, 22, 'sante', 'thermometre.jpg', 0.2, 'SAN-THE-001', 4.7, 28),
('Veilleuse bébé', 'Veilleuse avec projection d\'étoiles', 29.90, 30, 'bebe', 'veilleuse.jpg', 0.4, 'BEB-VEI-001', 4.8, 19);

-- ============================================
-- 9. ADD COMMANDES (orders)
-- ============================================
INSERT INTO `commande` (`date_commande`, `statut`, `total`, `email`, `telephone`, `shipping_address`, `shipping_city`, `shipping_postal_code`, `shipping_country`, `shipping_cost`, `shipping_carrier`, `shipping_eta_days`, `payment_status`, `paid_at`) VALUES
('2026-03-01 10:15:00', 'Livrée', 125.70, 'sarah.mansour@gmail.com', '+21698765432', 'Avenue Habib Bourguiba', 'Tunis', '1000', 'TN', 5.60, 'POSTE', 2, 'paid', '2026-03-01 10:20:00'),
('2026-03-02 14:30:00', 'En cours', 89.90, 'amina.benali@gmail.com', '+21697654321', 'Rue de Marseille', 'Sfax', '3000', 'TN', 8.40, 'ARAMEX', 1, 'paid', '2026-03-02 14:35:00'),
('2026-03-03 09:45:00', 'En attente', 154.80, 'nour.haddad@gmail.com', '+21696543210', 'Centre Ville', 'Sousse', '4000', 'TN', 5.60, 'POSTE', 2, 'pending_offline', NULL),
('2026-03-04 16:20:00', 'Livrée', 69.90, 'fatma.trabelsi@gmail.com', '+21695432109', 'Rue de la Liberté', 'Nabeul', '8000', 'TN', 5.60, 'POSTE', 3, 'paid', '2026-03-04 16:25:00'),
('2026-03-05 11:00:00', 'En cours', 214.60, 'imen.gharbi@gmail.com', '+21694321098', 'Avenue de la République', 'Mahdia', '5100', 'TN', 10.08, 'DHL', 2, 'paid', '2026-03-05 11:05:00');

-- ============================================
-- 10. LINK PRODUCTS TO COMMANDES
-- ============================================
INSERT INTO `commande_produit` (`commande_id`, `produit_id`) VALUES
(57, 7), -- Sarah: Tapis yoga + Ceinture
(57, 8),
(58, 9), -- Amina: Huile anti-vergetures + Coussin
(58, 10),
(59, 11), -- Nour: Biberons + Stérilisateur
(59, 12),
(60, 13), -- Fatma: Echarpe de portage
(61, 14), -- Imen: Couches + Thermomètre + Veilleuse
(61, 15),
(61, 16);

-- ============================================
-- 11. ADD BABY SITTER OFFERS
-- ============================================
INSERT INTO `offre_baby_sitter` (`nom_babysitter`, `telephone`, `experience`, `ville`, `tarif`, `description`, `disponibilite`) VALUES
('Fatma Mzoughi', '99123456', 5, 'Tunis', 35, 'Infirmière puéricultrice, expérience en crèche', 1),
('Samia Bouaziz', '98234567', 3, 'Sfax', 25, 'Étudiante en psychologie, adore les enfants', 1),
('Leila Khelifi', '97345678', 8, 'Sousse', 40, 'Ancienne institutrice maternelle', 1),
('Nadia Hammami', '96456789', 2, 'Nabeul', 20, 'Maman de deux enfants, disponible soirées', 1),
('Rim Trabelsi', '95567890', 4, 'Tunis', 30, 'Aide médico-psychologique', 1),
('Henda Mansour', '94678901', 6, 'Mahdia', 28, 'Expérience avec enfants en bas âge', 1);

-- ============================================
-- 12. ADD BABY SITTER REQUESTS
-- ============================================
INSERT INTO `demande_baby_sitter` (`nom_parent`, `email_parent`, `message`, `date_demande`, `statut`, `offre_id`) VALUES
('Sarah Mansour', 'sarah.mansour@gmail.com', 'Besoin d\'une baby-sitter pour mon bébé de 6 mois les jeudis après-midi', NOW() - INTERVAL 5 DAY, 'Acceptée', 12),
('Amina Ben Ali', 'amina.benali@gmail.com', 'Recherche baby-sitter pour garder ma fille de 2 ans en urgence ce week-end', NOW() - INTERVAL 3 DAY, 'En attente', 13),
('Nour Haddad', 'nour.haddad@gmail.com', 'Pour mes jumeaux de 1 an, besoin régulier les lundis et mercredis', NOW() - INTERVAL 2 DAY, 'Acceptée', 14),
('Fatma Trabelsi', 'fatma.trabelsi@gmail.com', 'Baby-sitter pour mon bébé de 8 mois, quelques heures par semaine', NOW() - INTERVAL 1 DAY, 'Refusée', 11),
('Imen Gharbi', 'imen.gharbi@gmail.com', 'Garde ponctuelle pour mon fils de 4 ans ce samedi soir', NOW(), 'En attente', 15);

-- ============================================
-- 13. ADD CONSULTATIONS (more)
-- ============================================
INSERT INTO `consultation` (`categorie`, `description`, `pour`, `image`, `icon`, `statut`, `ordre_affichage`, `created_at`, `updated_at`) VALUES
('Suivi post-partum', 'Consultations de suivi après l\'accouchement', 'MAMAN', 'post-partum.jpg', 'fas fa-heartbeat', 1, 4, NOW(), NOW()),
('Échographie 3D/4D', 'Échographies de dépistage et de bien-être', 'MAMAN', 'echo-3d.jpg', 'fas fa-son', 1, 5, NOW(), NOW()),
('Ostéopathie bébé', 'Séances d\'ostéopathie pour nourrissons', 'BEBE', 'osteo-bebe.jpg', 'fas fa-bone', 1, 4, NOW(), NOW()),
('Consultation diététique', 'Conseils nutritionnels pour futures mamans', 'LES_DEUX', 'dietetique.jpg', 'fas fa-carrot', 1, 3, NOW(), NOW());

-- ============================================
-- 14. ADD CONSULTATION CRENEAUX
-- ============================================
INSERT INTO `consultation_creneau` (`nom_medecin`, `photo_medecin`, `description_medecin`, `specialite_medecin`, `date_debut`, `date_fin`, `jour`, `heure_debut`, `heure_fin`, `statut_reservation`, `duree_minutes`, `nombre_places`, `created_at`, `updated_at`, `consultation_id`) VALUES
('Dr. Khaoula Ben Abdallah', 'dr-khaoula.jpg', 'Spécialiste en suivi post-partum', 'Gynécologue', '2026-03-10 09:00:00', '2026-03-10 17:00:00', NULL, NULL, NULL, 'DISPONIBLE', 45, 8, NOW(), NOW(), 12),
('Dr. Ahmed Feki', 'dr-feki.jpg', 'Radiologue spécialisé en échographie', 'Radiologue', '2026-03-12 08:00:00', '2026-03-12 16:00:00', NULL, NULL, NULL, 'DISPONIBLE', 30, 10, NOW(), NOW(), 13),
('Sophie Mrad', 'sophie.jpg', 'Ostéopathe D.O., spécialisée périnatalité', 'Ostéopathe', '2026-03-15 09:00:00', '2026-03-15 12:00:00', NULL, NULL, NULL, 'DISPONIBLE', 40, 6, NOW(), NOW(), 14),
('Mme. Nesrine Kacem', 'nesrine.jpg', 'Diététicienne nutritionniste', 'Diététicienne', '2026-03-18 10:00:00', '2026-03-18 14:00:00', NULL, NULL, NULL, 'DISPONIBLE', 50, 8, NOW(), NOW(), 15);

-- Weekly creneaux
INSERT INTO `consultation_creneau` (`nom_medecin`, `photo_medecin`, `description_medecin`, `specialite_medecin`, `jour`, `heure_debut`, `heure_fin`, `statut_reservation`, `duree_minutes`, `nombre_places`, `created_at`, `updated_at`, `consultation_id`) VALUES
('Dr. Khaoula Ben Abdallah', 'dr-khaoula.jpg', 'Consultations post-partum', 'Gynécologue', '2026-03-10', '09:00:00', '12:00:00', 'DISPONIBLE', 45, 4, NOW(), NOW(), 12),
('Dr. Khaoula Ben Abdallah', 'dr-khaoula.jpg', 'Consultations post-partum', 'Gynécologue', '2026-03-17', '09:00:00', '12:00:00', 'DISPONIBLE', 45, 4, NOW(), NOW(), 12),
('Dr. Ahmed Feki', 'dr-feki.jpg', 'Échographies', 'Radiologue', '2026-03-12', '08:00:00', '16:00:00', 'DISPONIBLE', 30, 8, NOW(), NOW(), 13),
('Dr. Ahmed Feki', 'dr-feki.jpg', 'Échographies', 'Radiologue', '2026-03-19', '08:00:00', '16:00:00', 'DISPONIBLE', 30, 8, NOW(), NOW(), 13);

-- ============================================
-- 15. ADD RESERVATIONS
-- ============================================
INSERT INTO `reservation_client` (`nom_client`, `prenom_client`, `email_client`, `telephone_client`, `type_patient`, `mois_grossesse`, `statut_reservation`, `reference`, `date_reservation`, `notes`, `created_at`, `updated_at`, `consultation_creneau_id`) VALUES
('Mansour', 'Sarah', 'sarah.mansour@gmail.com', '98765432', 'MAMAN', 5, 'CONFIRME', CONCAT('RDV-', UNIX_TIMESTAMP()), NOW() - INTERVAL 2 DAY, 'Première échographie', NOW(), NOW(), 18),
('Ben Ali', 'Amina', 'amina.benali@gmail.com', '97654321', 'MAMAN', 6, 'CONFIRME', CONCAT('RDV-', UNIX_TIMESTAMP()), NOW() - INTERVAL 1 DAY, 'Suivi post-partum', NOW(), NOW(), 19),
('Haddad', 'Nour', 'nour.haddad@gmail.com', '96543210', 'MAMAN', 7, 'CONFIRME', CONCAT('RDV-', UNIX_TIMESTAMP()), NOW(), 'Consultation jumeaux', NOW(), NOW(), 20);

-- ============================================
-- 16. ADD PROMO CODES
-- ============================================
INSERT INTO `promo_code` (`code`, `discount_percent`, `email`, `is_used`, `created_at`, `used_at`) VALUES
('BIENVENUE10', 10, NULL, 0, NOW(), NULL),
('MAMAN15', 15, NULL, 0, NOW(), NULL),
('NAISSANCE20', 20, NULL, 0, NOW(), NULL),
('PARRAINAGE', 15, 'sarah.mansour@gmail.com', 0, NOW(), NULL),
('FIDELITE', 10, 'amina.benali@gmail.com', 0, NOW(), NULL);

-- ============================================
-- 17. UPDATE AUTO_INCREMENT VALUES
-- ============================================
-- No need to update, AUTO_INCREMENT will handle itself

-- ============================================
-- 18. VERIFICATION QUERIES
-- ============================================
SELECT 'USERS' as table_name, COUNT(*) as record_count FROM `user`;
SELECT 'MAMAN' as table_name, COUNT(*) as record_count FROM `maman`;
SELECT 'GROSSESSE' as table_name, COUNT(*) as record_count FROM `grosesse`;
SELECT 'EVENTS' as table_name, COUNT(*) as record_count FROM `event`;
SELECT 'ATTENDANCE' as table_name, COUNT(*) as record_count FROM `attendance`;
SELECT 'PRODUITS' as table_name, COUNT(*) as record_count FROM `produit`;
SELECT 'COMMANDES' as table_name, COUNT(*) as record_count FROM `commande`;
SELECT 'BABY_SITTER_OFFRES' as table_name, COUNT(*) as record_count FROM `offre_baby_sitter`;
SELECT 'BABY_SITTER_DEMANDES' as table_name, COUNT(*) as record_count FROM `demande_baby_sitter`;
SELECT 'CONSULTATIONS' as table_name, COUNT(*) as record_count FROM `consultation`;
SELECT 'CRENEAUX' as table_name, COUNT(*) as record_count FROM `consultation_creneau`;
SELECT 'RESERVATIONS' as table_name, COUNT(*) as record_count FROM `reservation_client`;
SELECT 'PROMO_CODES' as table_name, COUNT(*) as record_count FROM `promo_code`;

-- ============================================
-- FILL REQUIREMENT TABLE AND LINK TO EVENTS
-- ============================================

-- 19. First, make sure we have all requirements (you already have 1-10)
-- Add any missing requirements
INSERT INTO `requirement` (`name`) VALUES
('Tapis de yoga'),
('Bouteille d\'eau'),
('Tenue confortable'),
('Serviette'),
('Coussin'),
('Carnet de notes'),
('Biberon'),
('Poussette'),
('Lingettes'),
('Couches'),
('Echarpe de portage'), -- New requirement
('Jouets'), -- New requirement
('Coussin d\'allaitement'); -- New requirement

-- ============================================
-- 2. LINK REQUIREMENTS TO EVENTS (event_requirement table)
-- ============================================

-- Event 1: sqdsqd (ID: 1) - Atelier bébé
INSERT INTO `event_requirement` (`event_id`, `requirement_id`) VALUES
(1, 1), -- Tapis de yoga
(1, 3), -- Tenue confortable
(1, 6); -- Carnet de notes

-- Event 2: test event new (ID: 2) - Atelier bébé
INSERT INTO `event_requirement` (`event_id`, `requirement_id`) VALUES
(2, 2), -- Bouteille d'eau
(2, 3), -- Tenue confortable
(2, 4), -- Serviette
(2, 5); -- Coussin

-- Event 24: sqdsqdfgfdg (ID: 24) - Atelier bébé
INSERT INTO `event_requirement` (`event_id`, `requirement_id`) VALUES
(24, 1), -- Tapis de yoga
(24, 2); -- Bouteille d'eau

-- Event 25: gfdgdfghhfg (ID: 25) - Atelier bébé
INSERT INTO `event_requirement` (`event_id`, `requirement_id`) VALUES
(25, 2), -- Bouteille d'eau
(25, 4); -- Serviette

-- Event 26: fdsgfdgfdsgfdsgfds (ID: 26) - Activités familiales
INSERT INTO `event_requirement` (`event_id`, `requirement_id`) VALUES
(26, 2), -- Bouteille d'eau
(26, 3), -- Tenue confortable
(26, 6); -- Carnet de notes

-- ============================================
-- 3. LINK REQUIREMENTS TO NEW EVENTS (from test data script)
-- ============================================

-- Yoga prénatal (ID: 27)
INSERT INTO `event_requirement` (`event_id`, `requirement_id`) VALUES
(27, 1), -- Tapis de yoga
(27, 2), -- Bouteille d'eau
(27, 3); -- Tenue confortable

-- Cours d'allaitement (ID: 28)
INSERT INTO `event_requirement` (`event_id`, `requirement_id`) VALUES
(28, 6), -- Carnet de notes
(28, 13); -- Coussin d'allaitement

-- Café des mamans (ID: 29)
INSERT INTO `event_requirement` (`event_id`, `requirement_id`) VALUES
(29, 6); -- Carnet de notes

-- Atelier portage (ID: 30)
INSERT INTO `event_requirement` (`event_id`, `requirement_id`) VALUES
(30, 11); -- Echarpe de portage

-- Massage bébé (ID: 31)
INSERT INTO `event_requirement` (`event_id`, `requirement_id`) VALUES
(31, 5), -- Coussin
(31, 4); -- Serviette

-- Promenade entre mamans (ID: 32)
INSERT INTO `event_requirement` (`event_id`, `requirement_id`) VALUES
(32, 2), -- Bouteille d'eau
(32, 3); -- Tenue confortable

-- Yoga hebdomadaire (ID: 33)
INSERT INTO `event_requirement` (`event_id`, `requirement_id`) VALUES
(33, 1), -- Tapis de yoga
(33, 2), -- Bouteille d'eau
(33, 3); -- Tenue confortable

-- Groupe de parole (ID: 34)
INSERT INTO `event_requirement` (`event_id`, `requirement_id`) VALUES
(34, 6); -- Carnet de notes

-- ============================================
-- 4. VERIFICATION QUERIES
-- ============================================

-- Check all requirements
SELECT 'REQUIREMENTS' as table_name, COUNT(*) as count FROM `requirement`;

-- Show events with their requirements
SELECT 
    e.id as event_id,
    e.title as event_title,
    GROUP_CONCAT(r.name SEPARATOR ', ') as requirements
FROM event e
LEFT JOIN event_requirement er ON e.id = er.event_id
LEFT JOIN requirement r ON er.requirement_id = r.id
GROUP BY e.id
ORDER BY e.id;

-- Show all event-requirement links
SELECT 
    er.event_id,
    e.title as event_title,
    er.requirement_id,
    r.name as requirement_name
FROM event_requirement er
JOIN event e ON er.event_id = e.id
JOIN requirement r ON er.requirement_id = r.id
ORDER BY er.event_id, er.requirement_id;