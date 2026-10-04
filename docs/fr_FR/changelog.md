# Changelog plugin M5Dial

## 0.4

- État affiché sur la vignette de chaque bouton : En ligne / Hors ligne, version du firmware et source de la configuration.

## 0.3

- Nouvel onglet « Écrans du bouton » : éditeur par écran (Présence, Lumières, Volets, Actions groupées, Chauffage, Capteurs, Badges) avec le sélecteur de commandes Jeedom. Le JSON est généré automatiquement (onglet JSON).

## 0.2

- Correction : « La configuration est vide » à l'envoi (Jeedom convertit le JSON en tableau à la sauvegarde). Le bouton Envoyer utilise directement le contenu du champ.

## 0.1

- Détection automatique des boutons, commandes de supervision (en ligne, version, IP, signal Wi-Fi, mémoire…).
- Envoi de la configuration JSON au bouton et retour à la configuration locale.
