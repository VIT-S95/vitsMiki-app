<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class KizeoFieldMappingsSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('kizeo_field_mappings')->insertOrIgnore([
            // Formulaire 45252 — Site
            ['form_id' => '45252', 'kizeo_field' => '_id', 'app_field' => 'numero_bon_kizeo', 'transform' => null, 'notes' => 'Identifiant unique'],
            ['form_id' => '45252', 'kizeo_field' => 'date', 'app_field' => 'date_intervention', 'transform' => null, 'notes' => 'Date intervention'],
            ['form_id' => '45252', 'kizeo_field' => '_answer_time', 'app_field' => 'heure_intervention', 'transform' => 'substr_11_5', 'notes' => 'Extrait HH:MM'],
            ['form_id' => '45252', 'kizeo_field' => '_last_name', 'app_field' => 'technicien', 'transform' => 'concat_prenom_nom', 'notes' => 'Combiné avec _first_name'],
            ['form_id' => '45252', 'kizeo_field' => '_first_name', 'app_field' => 'technicien', 'transform' => 'concat_prenom_nom', 'notes' => 'Combiné avec _last_name'],
            ['form_id' => '45252', 'kizeo_field' => 'client', 'app_field' => 'client_nom', 'transform' => null, 'notes' => 'Liste déroulante'],
            ['form_id' => '45252', 'kizeo_field' => 'autre_client', 'app_field' => 'pending_client', 'transform' => 'file_attente', 'notes' => 'Client inconnu → file attente admin'],
            ['form_id' => '45252', 'kizeo_field' => 'intervention', 'app_field' => 'statut', 'transform' => 'cloture_ancien', 'notes' => 'clôturée → traitee'],
            ['form_id' => '45252', 'kizeo_field' => 'temps', 'app_field' => 'duree_minutes', 'transform' => 'heures_fois_60', 'notes' => 'Tranches 60min'],
            ['form_id' => '45252', 'kizeo_field' => 'temps_du_devis', 'app_field' => 'duree_devis_minutes', 'transform' => 'heures_fois_60', 'notes' => ''],
            ['form_id' => '45252', 'kizeo_field' => 'contrat', 'app_field' => 'deductible', 'transform' => 'logique_deductible', 'notes' => 'Combiné avec forfait + flash'],
            ['form_id' => '45252', 'kizeo_field' => 'forfait', 'app_field' => 'deductible', 'transform' => 'logique_deductible', 'notes' => ''],
            ['form_id' => '45252', 'kizeo_field' => 'flash', 'app_field' => 'type', 'transform' => 'logique_flash', 'notes' => 'oui → type=flash'],
            ['form_id' => '45252', 'kizeo_field' => 'commentaires', 'app_field' => 'commentaires', 'transform' => null, 'notes' => ''],
            ['form_id' => '45252', 'kizeo_field' => 'pieces_detachees', 'app_field' => 'pieces_detachees', 'transform' => null, 'notes' => ''],
            ['form_id' => '45252', 'kizeo_field' => 'bool_annexe', 'app_field' => 'demande_annexe', 'transform' => null, 'notes' => ''],
            ['form_id' => '45252', 'kizeo_field' => 'demande_annexe', 'app_field' => 'demande_annexe', 'transform' => null, 'notes' => ''],
            ['form_id' => '45252', 'kizeo_field' => 'intervention_en_dehors_des_he', 'app_field' => 'hors_heure_ouvree', 'transform' => 'bool_oui_non', 'notes' => ''],
            ['form_id' => '45252', 'kizeo_field' => 'ref_contrat', 'app_field' => null, 'transform' => 'raw_data', 'notes' => 'Ancien indicateur liste Kizeo'],
            ['form_id' => '45252', 'kizeo_field' => 'ce_client_n_a_pas_de_contrat', 'app_field' => null, 'transform' => 'raw_data', 'notes' => 'Info terrain uniquement'],
            ['form_id' => '45252', 'kizeo_field' => 'n_de_devis', 'app_field' => 'n_devis', 'transform' => null, 'notes' => ''],
            ['form_id' => '45252', 'kizeo_field' => 'demandeur', 'app_field' => 'donneur_ordre', 'transform' => null, 'notes' => ''],

            // Formulaire 108738 — Distance
            ['form_id' => '108738', 'kizeo_field' => '_id', 'app_field' => 'numero_bon_kizeo', 'transform' => null, 'notes' => 'Identifiant unique'],
            ['form_id' => '108738', 'kizeo_field' => 'date', 'app_field' => 'date_intervention', 'transform' => null, 'notes' => ''],
            ['form_id' => '108738', 'kizeo_field' => '_answer_time', 'app_field' => 'heure_intervention', 'transform' => 'substr_11_5', 'notes' => 'Extrait HH:MM'],
            ['form_id' => '108738', 'kizeo_field' => '_last_name', 'app_field' => 'technicien', 'transform' => 'concat_prenom_nom', 'notes' => 'Combiné avec _first_name'],
            ['form_id' => '108738', 'kizeo_field' => '_first_name', 'app_field' => 'technicien', 'transform' => 'concat_prenom_nom', 'notes' => 'Combiné avec _last_name'],
            ['form_id' => '108738', 'kizeo_field' => 'client', 'app_field' => 'client_nom', 'transform' => null, 'notes' => ''],
            ['form_id' => '108738', 'kizeo_field' => 'autre_client', 'app_field' => 'pending_client', 'transform' => 'file_attente', 'notes' => 'Client inconnu → file attente admin'],
            ['form_id' => '108738', 'kizeo_field' => 'intervention', 'app_field' => 'statut', 'transform' => 'cloture_ancien', 'notes' => 'clôturée → traitee'],
            ['form_id' => '108738', 'kizeo_field' => 'forfait_temps', 'app_field' => 'duree_minutes', 'transform' => 'parse_duree_20', 'notes' => 'Tranches 20min'],
            ['form_id' => '108738', 'kizeo_field' => 'temps', 'app_field' => 'duree_minutes', 'transform' => 'parse_duree_20', 'notes' => 'Fallback si forfait_temps vide'],
            ['form_id' => '108738', 'kizeo_field' => 'contrat', 'app_field' => 'deductible', 'transform' => 'logique_deductible', 'notes' => ''],
            ['form_id' => '108738', 'kizeo_field' => 'forfait', 'app_field' => 'deductible', 'transform' => 'logique_deductible', 'notes' => ''],
            ['form_id' => '108738', 'kizeo_field' => 'flash', 'app_field' => 'type', 'transform' => 'logique_flash', 'notes' => ''],
            ['form_id' => '108738', 'kizeo_field' => 'commentaires', 'app_field' => 'commentaires', 'transform' => null, 'notes' => ''],
            ['form_id' => '108738', 'kizeo_field' => 'heures_ouvrees', 'app_field' => 'hors_heure_ouvree', 'transform' => 'bool_oui_non', 'notes' => ''],
            ['form_id' => '108738', 'kizeo_field' => 'ref_contrat', 'app_field' => null, 'transform' => 'raw_data', 'notes' => 'Ancien indicateur liste Kizeo'],
            ['form_id' => '108738', 'kizeo_field' => 'ce_client_n_a_pas_de_contrat_', 'app_field' => null, 'transform' => 'raw_data', 'notes' => 'Underscore final'],
            ['form_id' => '108738', 'kizeo_field' => 'n_de_devis', 'app_field' => 'n_devis', 'transform' => null, 'notes' => ''],
            ['form_id' => '108738', 'kizeo_field' => 'ticket', 'app_field' => 'n_ticket', 'transform' => null, 'notes' => ''],
            ['form_id' => '108738', 'kizeo_field' => 'demandeur', 'app_field' => 'donneur_ordre', 'transform' => null, 'notes' => ''],

            // Nouveau formulaire
            ['form_id' => '1185604', 'kizeo_field' => '_id', 'app_field' => 'numero_bon_kizeo', 'transform' => null, 'notes' => ''],
            ['form_id' => '1185604', 'kizeo_field' => 'date', 'app_field' => 'date_intervention', 'transform' => null, 'notes' => ''],
            ['form_id' => '1185604', 'kizeo_field' => '_answer_time', 'app_field' => 'heure_intervention', 'transform' => 'substr_11_5', 'notes' => ''],
            ['form_id' => '1185604', 'kizeo_field' => '_last_name', 'app_field' => 'technicien', 'transform' => 'concat_prenom_nom', 'notes' => ''],
            ['form_id' => '1185604', 'kizeo_field' => '_first_name', 'app_field' => 'technicien', 'transform' => 'concat_prenom_nom', 'notes' => ''],
            ['form_id' => '1185604', 'kizeo_field' => 'societe', 'app_field' => 'client_nom', 'transform' => null, 'notes' => 'Liste déroulante alimentée par app'],
            ['form_id' => '1185604', 'kizeo_field' => 'autre_societe', 'app_field' => 'pending_client', 'transform' => 'file_attente', 'notes' => 'Client inconnu → file attente admin'],
            ['form_id' => '1185604', 'kizeo_field' => 'inter_cloture', 'app_field' => 'statut', 'transform' => 'cloture_nouveau', 'notes' => '1 → traitee'],
            ['form_id' => '1185604', 'kizeo_field' => 'type_intervention', 'app_field' => 'type', 'transform' => 'type_nouveau', 'notes' => 'Sur site, A distance, Atelier'],
            ['form_id' => '1185604', 'kizeo_field' => 'flash', 'app_field' => 'type', 'transform' => 'logique_flash', 'notes' => ''],
            ['form_id' => '1185604', 'kizeo_field' => 'duree_inter_site_1', 'app_field' => 'duree_minutes', 'transform' => 'heures_fois_60', 'notes' => 'Si type = Sur site'],
            ['form_id' => '1185604', 'kizeo_field' => 'duree_inter_distant_1', 'app_field' => 'duree_minutes', 'transform' => 'parse_duree_20', 'notes' => 'Si type = A distance, sauf autre'],
            ['form_id' => '1185604', 'kizeo_field' => 'duree_inter_distant_2', 'app_field' => 'duree_minutes', 'transform' => 'parse_duree_20', 'notes' => 'Si duree_inter_distant_1 = autre'],
            ['form_id' => '1185604', 'kizeo_field' => 'temps_complementaire', 'app_field' => 'duree_devis_minutes', 'transform' => 'heures_fois_60', 'notes' => ''],
            ['form_id' => '1185604', 'kizeo_field' => 'contrat', 'app_field' => 'deductible', 'transform' => 'logique_deductible', 'notes' => ''],
            ['form_id' => '1185604', 'kizeo_field' => 'forfait_devis', 'app_field' => 'deductible', 'transform' => 'logique_deductible', 'notes' => 'Remplace forfait'],
            ['form_id' => '1185604', 'kizeo_field' => 'commentaires', 'app_field' => 'commentaires', 'transform' => null, 'notes' => ''],
            ['form_id' => '1185604', 'kizeo_field' => 'hors_heure_ouvree', 'app_field' => 'hors_heure_ouvree', 'transform' => 'bool_oui_non', 'notes' => ''],
            ['form_id' => '1185604', 'kizeo_field' => 'donneur_ordre', 'app_field' => 'donneur_ordre', 'transform' => null, 'notes' => ''],
            ['form_id' => '1185604', 'kizeo_field' => 'n_ticket', 'app_field' => 'n_ticket', 'transform' => null, 'notes' => ''],
            ['form_id' => '1185604', 'kizeo_field' => 'n_devis', 'app_field' => 'n_devis', 'transform' => null, 'notes' => ''],
            ['form_id' => '1185604', 'kizeo_field' => 'ce_client_n_a_pas_de_contrat', 'app_field' => null, 'transform' => 'raw_data', 'notes' => 'Info terrain uniquement'],
        ]);
    }
}
