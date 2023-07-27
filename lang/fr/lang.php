<?php

return [
    'plugin' => [
        'name' => 'Adhésion',
        'description' => 'Plugin qui gére les adhérents de l\'association Expertij.'
    ],
    'membership' => [
      'menu_label' => 'Adhésion',
      'members' => 'Adhérents',
      'categories' => 'Catégories',
      'documents' => 'Documents',
      'member_space' => 'Espace adhérent',
      'member_list' => 'Liste des adhérents',
      'honorary_member_list' => 'Liste des membres d\'honneur',
    ],
    'member' => [
      'tab_profile' => 'Profil',
      'tab_licences' => 'Licences',
      'tab_pro_status' => 'Statut professionnel',
      'tab_votes' => 'Votes',
      'tab_payments' => 'Paiements',
      'tab_insurance' => 'Assurance',
      'tab_informations' => 'Informations',
      'tab_invoices' => 'Factures',
      'tab_payment' => 'Paiement',
      'tab_documents' => 'Documents',
      'first_name' => 'Prénom',
      'last_name' => 'Nom',
      'email' => 'Email',
      'member_number' => 'Numéro membre',
      'appeal_court' => 'Cour d\'appel',
      'languages_expert' => 'Languages (Expert)',
      'languages_ceseda' => 'Languages (CESEDA)',
      'honorary_member_info_1' => 'Les membres d\'honneur n\'ont pas de licences.',
      'honorary_member_info_2' => 'Les membres d\'honneur n\'ont pas de statut professionnel.',
      'no_longer_member' => 'Vous n\'êtes plus adhérent(e).',
    ],
    'members' => [
      'filter_status' => 'Statut',
    ],
    'categories' => [
    ],
    'documents' => [
      'recipients' => 'Destinataires',
    ],
    'document' => [
      'tab_filters' => 'Filtres',
      'appeal_courts' => 'Cours d\'appel',
      'courts' => 'Cours',
      'languages' => 'Languages',
      'licence_types' => 'Types de licence',
      'last_email_sending' => 'Dernier email envoyé',
    ],
    // Boilerplate attributes.
    'attribute' => [
      'title' => 'Titre',
      'name' => 'Nom',
      'slug' => 'Slug',
      'type' => 'Type',
      'code' => 'Code',
      'date' => 'Date',
      'item' => 'Item',
      'required' => 'Requis',
      'yes' => 'Oui',
      'no' => 'Non',
      'all' => 'Tout',
      'description' => 'Description',
      'title_placeholder' => 'Nouveau titre d\'item',
      'name_placeholder' => 'Nouveau nom d\'item',
      'code_placeholder' => 'Nouveau code d\'item',
      'slug_placeholder' => 'Nouveau slug d\'item',
      'created_at' => 'Créé le',
      'created_by' => 'Créé par',
      'updated_at' => 'Mis à jour le',
      'updated_by' => 'Mis à jour par',
      'tab_edit' => 'Edition',
      'tab_manage' => 'Gestion',
      'tab_categories' => 'Catégories',
      'status' => 'Statut',
      'published_up' => 'Début de publication',
      'published_down' => 'Fin de publication',
      'access' => 'Accès',
      'viewing_access' => 'Accès lecture',
      'category' => 'Catégorie',
      'field_group' => 'Groupe de champs',
      'main_category' => 'Catégorie principale',
      'parent_category' => 'Catégorie parent',
      'none' => 'Aucun',
      'information' => 'Information',
    ],
    'status' => [
      'published' => 'Publié',
      'unpublished' => 'Dépublié',
      'trashed' => 'Dans la poubelle',
      'archived' => 'Archivé',
      'pending' => 'En attente',
      'refused' => 'Refusé',
      'pending_subscription' => 'En attente d\'adhésion',
      'completed' => 'Terminé',
      'cancelled' => 'Annulé',
      'error' => 'Erreur',
      'member' => 'Membre',
      'pending_renewal' => 'En attente de renouvellement',
      'revoked' => 'Revoqué',
      'cancellation' => 'annulation',
    ],
    'action' => [
      'new_document' => 'Nouveau document',
      'publish' => 'Publier',
      'unpublish' => 'Dépublier',
      'trash' => 'Poubelle',
      'archive' => 'Archiver',
      'delete' => 'Supprimer',
      'save' => 'Sauvegarder',
      'reset' => 'Réinitialiser',
      'update' => 'Mettre à jour',
      'replace' => 'Remplacer',
      'validate' => 'Valider',
      'return' => 'Revenir',
      'save_and_close' => 'Sauvegarder et fermer',
      'create' => 'Créer',
      'create_and_close' => 'Créer et fermer',
      'edit' => 'Editer',
      'cancel' => 'Annuler',
      'check_in' => 'Check-in',
      'check_renewal' => 'Vérifier renouvellement',
      'select' => '- Selectionner -',
      'all' => '- Tout -',
      'pay_now' => 'Payer maintenant',
      'pay_cheque_confirmation' => 'Vous avez choisi de payer par chèque. Vous allez recevoir un email avec toutes les informations nécessaires. Souhaitez vous continuer ?',
      'pay_paypal_confirmation' => 'Vous avez choisi de payer via PayPal. Vous allez être redirigé(e) vers PayPal. Souhaitez vous continuer ?',
      'pay_sherlocks_confirmation' => 'Vous avez choisi de payer via LCL. Une carte de credit va vous être proposée. Souhaitez vous continuer ?',
      'pay_free_period_confirmation' => 'Compte tenu de votre adhésion tardive pour cette année, l\'adhésion pour l\'année suivante est gratuite. Souhaitez vous continuer ?',
      'subscribe' => 'Devenir adhérent',
      'export' => 'Exporter',
      'publish_success' => ':count item(s) publié(s) avec succès.',
      'unpublish_success' => ':count item(s) dépublié(s) avec succès.',
      'archive_success' => ':count item(s) archivé avec succès.',
      'trash_success' => ':count item(s) mis dans la corbeille avec succès.',
      'delete_success' => ':count item(s) supprimé(e) avec succès.',
      'check_in_success' => ':count item(s) successfully checked-in.',
      'revocation_success' => ':count adhérent(s)  revoqué(s) avec succès.',
      'update_success' => 'Les données ont été mise à jour avec succès.',
      'parent_item_unpublished' => 'Impossible de publier cet item car son item parent est dépublié.',
      'previous' => 'Précédent',
      'next' => 'Suivant',
      'deletion_confirmation' => 'Etes vous sûr(e) de vouloir supprimer les items sélectionnés ?',
      'cannot_reorder' => 'Impossible de réordonner les items par catégorie car aucune ou plus d\'une categories ont été sélectionnées. Veuillez ne sélectionner qu\'une seule catégorie.',
      'checked_out_item' => 'The ":name" item cannot be modified as it is currently checked out by a user.',
      'check_out_do_not_match' => 'The user checking out doesn\'t match the user who checked out the item. You are not permitted to use that link to directly access that page.',
      'editing_not_allowed' => 'Vous n\'êtes pas autorisé(e) a éditer cet item.',
      'used_as_main_category' => 'La catégorie ":name" car elle est utilisée comme catégorie principale dans un ou plusieurs items.',
      'not_allowed_to_modify_item' => 'Vous n\'êtes pas autorisé(e) a modifier l\'item ":name".',
      'profile_update_success' => 'Le profil de l\'adhérent(e) a été mis à jour avec succès.',
      'vote_success' => 'Votre vote a été pris en compte avec succès.',
      'vote_confirmation' => 'Votre vote est sur le point d\'être validé. Etes vous sûr(e) ?',
      'payment_confirmation' => 'Le paiement est sur le point d\'être pris en compte. Etes vous sûr(e) ?',
      'file_replace_success' => 'Le fichier a été remplacé avec succès.',
      'cheque_payment_success' => 'Votre paiement par chèque a été pris en compte avec succès.',
      'bank_transfer_payment_success' => 'Votre paiement par transfert banquaire a été pris en compte avec succès.',
      'free_period_privilege_success' => 'Votre privilège de gratuité a été pris en compte avec succès.',
      'payment_update_success' => 'Le paiement a été mis à jour avec succès.',
      'status_changed_by_system' => 'Le statut a été changé par le système. Vous ne pouvez pas sauvegarder le formulaire. Veuillez rafraîchir la page (touche F5) et le sauvegarder à nouveau.',
      'status_change_confirmation' => 'Le statut de l\'adhérent(e) est sur le point d\'être changé. Etes vous sûr(e) ?',
      'state_change_confirmation' => 'L\'état de l\'item est sur le point d\'être changé. Etes vous sûr(e) ?',
      'email_sendings_count' => 'Emails envoyés (:count)',
      'email_sendings' => 'Envoi d\'emails',
      'email_sending_confirmation' => 'Un email est sur le point d\'être envoyé à un ou plusieurs destinataires. Etes vous sûr(e) ?',
      'email_sendings_success' => 'Les emails ont été envoyés avec succès au(x) destinataire(s)',
      'delete_file_success' => 'Le fichier a été supprimé avec succès.',
      'no_file_selected' => 'Aucun fichier sélectionné.',
      'check_renewal_renewal_success' => 'La période de renouvellement a démarré et un email a été envoyé aux adhérents.',
      'check_renewal_reminder_success' => 'Un email de rappel a été envoyé aux adhérents qui n\'ont pas encore payé leur cotisation.',
      'check_renewal_delete_renewal_job_success' => 'Le job de renouvellement a été supprimé.',
      'check_renewal_none_success' => 'Aucune action n\'a été effectuée.',
    ],
    'email' => [
      'your_application' => 'Votre candidature',
      'new_application' => 'Nouvelle candidature',
      'new_vote' => 'Nouveau vote',
      'new_document' => 'Nouveau document',
      'new_member' => 'Vous êtes adhérent(e) !',
      'pending_renewal' => 'Renouvellement adhésion',
      'pending_renewal_reminder' => 'Rappel renouvellement adhésion',
      'pending_renewal_last_reminder' => 'Dernier rappel renouvellement adhésion',
      'pending_subscription' => 'Candidature acceptée',
      'refused' => 'Candidature refusée',
      'renewal_subscription' => 'Adhésion renouvelée',
      'revoked' => 'Adhésion révoquée',
      'cancelled' => 'Candidature annulée',
      'cancellation' => 'Annulation adhésion',
      'cheque_payment' => 'Confirmation paiement par chèque',
      'cheque_payment_subscription' => 'Confirmation paiement cotisation par chèque',
      'cheque_payment_insurance' => 'Confirmation paiement assurance par chèque',
      'cheque_payment_subscription_insurance' => 'Confirmation paiement cotisation et assurance par chèque',
      'alert_cheque_payment' => 'Notification paiement par chèque',
      'alert_cheque_payment_subscription' => 'Notification paiement cotisation par chèque',
      'alert_cheque_payment_insurance' => 'Notification paiement assurance par chèque',
      'alert_cheque_payment_subscription_insurance' => 'Notification paiement cotisation et assurance par chèque',
      'payment_completed' => 'Paiement terminé',
      'payment_completed_admin' => 'Paiement terminé',
      'free_period_validated' => 'Période de gratuité validée',
      'free_period_validated_admin' => 'Période de gratuité validée',
      'payment_error' => 'Erreur de paiement',
      'payment_error_admin' => 'Erreur de paiement',
      'payment_cancelled' => 'Paiement annulé',
      'payment_cancelled_admin' => 'Paiement annulé',
    ],
    'payment' => [
      'subscription' => 'Adhésion',
      'subscription-insurance-f1' => 'Adhésion + Assurance - Judiciaire option 1',
      'subscription-insurance-f2' => 'Adhésion + Assurance - Judiciaire option 2',
      'subscription-insurance-f3' => 'Adhésion + Assurance - Judiciaire option 3',
      'subscription-insurance-f4' => 'Adhésion + Assurance - Extrajudiciaire option 1',
      'subscription-insurance-f5' => 'Adhésion + Assurance - Extrajudiciaire option 2',
      'subscription-insurance-f6' => 'Adhésion + Assurance - Extrajudiciaire option 3',
      'subscription-insurance-f7' => 'Adhésion + Assurance - Judiciaire + Extrajudiciaire option 1',
      'subscription-insurance-f8' => 'Adhésion + Assurance - Judiciaire + Extrajudiciaire option 2',
      'subscription-insurance-f9' => 'Adhésion + Assurance - Judiciaire + Extrajudiciaire option 3',
      'insurance-f1' => 'Assurance - Judiciaire option 1',
      'insurance-f2' => 'Assurance - Judiciaire option 2',
      'insurance-f3' => 'Assurance - Judiciaire option 3',
      'insurance-f4' => 'Assurance - Extrajudiciaire option 1',
      'insurance-f5' => 'Assurance - Extrajudiciaire option 2',
      'insurance-f6' => 'Assurance - Extrajudiciaire option 3',
      'insurance-f7' => 'Assurance - Judiciaire + Extrajudiciaire option 1',
      'insurance-f8' => 'Assurance - Judiciaire + Extrajudiciaire option 2',
      'insurance-f9' => 'Assurance - Judiciaire + Extrajudiciaire option 3',
    ],
    'global_settings' => [
      'tab_general' => 'Général',
      'tab_prices' => 'Prix',
      'tab_images' => 'Images',
      'renewal_day' => 'Jour de renouvellement',
      'renewal_day_comment' => 'Définit le jour de renouvellement',
      'renewal_month' => 'Mois de renouvellement',
      'renewal_month_comment' => 'Définit le mois de renouvellement',
      'renewal_period' => 'Période de renouvellement',
      'renewal_period_comment' => 'Définit une période de renouvellement (en jours) avant la date de renouvellement',
      'free_period' => 'Période de gratuité',
      'free_period_comment' => 'Définit une période d\'adhésion (en jours) avant la date de renouvellement',
      'reminder_renewal' => 'Rappel renouvellement',
      'reminder_renewal_comment' => 'Définit un rappel (en jours) avant ou après la date de renouvellement. Pour définir un rappel x jours avant la date de renouvellement veuillez utiliser le signe moins. ex: -15',
      'subscription_fee' => 'Facture adhésion',
      'subscription_fee_comment' => 'Devise: Euro',
      'honorary_subscription_fee' => 'Facture adhésion membres d\'honneur',
      'honorary_subscription_fee_comment' => 'Devise: Euro',
      'insurance_fee_f1' => 'Facture assurance: Judiciaire option 1',
      'insurance_fee_f1_comment' => 'Devise: Euro',
      'insurance_fee_f2' => 'Facture assurance: Judiciaire option 2',
      'insurance_fee_f2_comment' => 'Devise: Euro',
      'insurance_fee_f3' => 'Facture assurance: Judiciaire option 3',
      'insurance_fee_f3_comment' => 'Devise: Euro',
      'insurance_fee_f4' => 'Facture assurance: Extrajudiciaire option 1',
      'insurance_fee_f4_comment' => 'Devise: Euro',
      'insurance_fee_f5' => 'Facture assurance: Extrajudiciaire option 2',
      'insurance_fee_f5_comment' => 'Devise: Euro',
      'insurance_fee_f6' => 'Facture assurance: Extrajudiciaire option 3',
      'insurance_fee_f6_comment' => 'Devise: Euro',
      'insurance_fee_f7' => 'Facture assurance: Judiciaire + Extrajudiciaire option 1',
      'insurance_fee_f7_comment' => 'Devise: Euro',
      'insurance_fee_f8' => 'Facture assurance: Judiciaire + Extrajudiciaire option 2',
      'insurance_fee_f8_comment' => 'Devise: Euro',
      'insurance_fee_f9' => 'Facture assurance: Judiciaire + Extrajudiciaire option 3',
      'insurance_fee_f9_comment' => 'Devise: Euro',
      'insurance_f1' => 'Judiciaire option 1',
      'insurance_f2' => 'Judiciaire option 2',
      'insurance_f3' => 'Judiciaire option 3',
      'insurance_f4' => 'Extrajudiciaire option 1',
      'insurance_f5' => 'Extrajudiciaire option 2',
      'insurance_f6' => 'Extrajudiciaire option 3',
      'insurance_f7' => 'Judiciaire + Extrajudiciaire option 1',
      'insurance_f8' => 'Judiciaire + Extrajudiciaire option 2',
      'insurance_f9' => 'Judiciaire + Extrajudiciaire option 3',
      'photo_thumbnail' => 'Vignette',
      'photo_thumbnail_comment' => 'La taille d\'une vignette en pixel. La largeur et la hauteur doivent être séparée par deux-points, (ex: 100:100).',
      'january' => 'Janvier',
      'february' => 'Février',
      'march' => 'Mars',
      'april' => 'Avril',
      'may' => 'Mai',
      'june' => 'Juin',
      'july' => 'Juillet',
      'august' => 'Août',
      'september' => 'Septembre',
      'october' => 'Octobre',
      'november' => 'Novembre',
      'december' => 'Décembre',
      'day_month_not_matching' => 'Le jour de renouvellement ne correspond pas au mois de renouvellement. Assurez vous que le mois choisi a 31 jours.',
      'reminder_renewal_too_high' => 'Le nombre de jours pour le rappel est supérieur ou égale au nombre de jour pour la période de renouvellement.',
      'settings_not_correctly_set' => 'Avertissement: Un ou plusieurs champs dans les paramètres généraux ne sont pas correctement définis.',
      'members_per_page' => 'Membres par page',
    ],
    'filter' => [
      'languages' => 'Languages',
      'type' => 'Type',
      'skill' => 'Compétence',
      'appeal_courts' => 'Cours d\'appel',
      'courts' => 'Cours',
    ],
    'professional_status' => [
      'pro_status' => 'Statut',
      'liberal_profession' => 'Profession libérale',
      'micro_entrepreneur' => 'Micro-entrepreneur',
      'company' => 'Société',
      'other' => 'Autre',
      'pro_status_info' => 'Information statut professionnel',
      'since' => 'Depuis',
      'siret_number' => 'Numéro SIRET',
      'naf_code' => 'Code NAF',
      'attestation' => 'Attestation',
      'linguistic_training' => 'Expérience linguistique',
      'extra_linguistic_training' => 'Expérience extra linguistique',
      'professional_experience' => 'Expérience professionnelle',
      'observations' => 'Observations',
      'why_expertij' => 'Pourquoi adhérer à Expertij ?',
      'code_of_ethics' => 'Code de déontologie',
      'statuses' => 'Statuts',
      'internal_rules' => 'Réglement interne',
    ],
    'votes' => [
      'voter' => 'Electeur',
      'note' => 'Remarques',
      'choice' => 'Choix',
      'no_vote' => 'Il n\'y a aucun vote à afficher',
    ],
    'payments' => [
      'payment_mode' => 'Mode de paiement',
      'amount' => 'Montant',
      'no_payment' => 'Il n\'y a aucun paiement à afficher',
      'pending_payment' => 'Votre paiement est actuellement en attente de validation.',
      'sherlocks' => 'LCI',
      'cheque' => 'Chèque',
      'free_period' => 'Période gratuite',
      'item' => [
	'subscription' => 'Adhésion',
	'insurance-f1' => 'Judiciaire option 1',
	'insurance-f2' => 'Judiciaire option 2',
	'insurance-f3' => 'Judiciaire option 3',
	'insurance-f4' => 'Extrajudiciaire option 1',
	'insurance-f5' => 'Extrajudiciaire option 2',
	'insurance-f6' => 'Extrajudiciaire option 3',
	'insurance-f7' => 'Judiciaire + Extrajudiciaire option 1',
	'insurance-f8' => 'Judiciaire + Extrajudiciaire option 2',
	'insurance-f9' => 'Judiciaire + Extrajudiciaire option 3',
	'subscription-insurance-f1' => 'Adhésion + Judiciaire option 1',
	'subscription-insurance-f2' => 'Adhésion + Judiciaire option 2',
	'subscription-insurance-f3' => 'Adhésion + Judiciaire option 3',
	'subscription-insurance-f4' => 'Adhésion + Extrajudiciaire option 1',
	'subscription-insurance-f5' => 'Adhésion + Extrajudiciaire option 2',
	'subscription-insurance-f6' => 'Adhésion + Extrajudiciaire option 3',
	'subscription-insurance-f7' => 'Adhésion + Judiciaire + Extrajudiciaire option 1',
	'subscription-insurance-f8' => 'Adhésion + Judiciaire + Extrajudiciaire option 2',
	'subscription-insurance-f9' => 'Adhésion + Judiciaire + Extrajudiciaire option 3',
      ]
    ],
    'insurance' => [
      'no_insurance' => 'Cet adhérent n\'a pas encore d\'assurance ou bien son assurance n\'est plus active.',
      'running_insurance' => 'Cet adhérent souscrit à l\'assurance suivante: ',
      'finishing_insurance' => 'L\'assurance de cet adhérent est sur le point de se terminer: ',
    ],
    'profile' => [
      'honorary_member' => 'Membre d\'honneur',
      'appeal_court' => 'Cour d\'appel',
      'court' => 'Cour',
      'interpreter' => 'Interprète',
      'translator' => 'Traducteur',
      'cassation' => 'Cassation',
    ]
];

