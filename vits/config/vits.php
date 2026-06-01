<?php
return [
    'motifs_intervention' => array (
  0 => 'Dépassement de quota n-1',
  1 => 'Intervention proactive',
  2 => 'Mise à jour planifiée',
  3 => 'Urgence hors contrat',
  4 => 'Prestation complémentaire',
),
    'seuil_echeance_mois' => 3,
    'seuil_heures_pct'    => 90,
    'kizeo_frequence_min' => 60,
    'session_minutes'      => 30,
    'kizeo_api_key'       => env('KIZEO_API_KEY', ''),
];
