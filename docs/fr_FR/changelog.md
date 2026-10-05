# Changelog plugin M5Dial

## 1.0

- Un bouton déjà connu (même adresse MAC) qui revient sous un autre nom, après une réinitialisation ou un réappairage, retrouve automatiquement son équipement et sa configuration.
- Vérification chaque nuit de la dernière version du firmware publiée sur GitHub : message Jeedom, badge « MAJ dispo » sur la vignette et nouvelle commande info « Mise à jour disponible ».
- Option « Installer automatiquement les nouvelles versions » (désactivée par défaut).
- « Déposer un firmware.bin » réservé au mode avancé (configuration du plugin).

## 0.9.3

- Documentation illustrée (captures d'écran Jeedom, photos du bouton, portail).

## 0.9.2

- Boutons Documentation et Changelog de Jeedom : liens vers les pages GitHub du plugin.

## 0.9.1

- Documentation complète : installation, portail Wi-Fi, appairage, écrans, mises à jour du firmware, topics MQTT, dépannage.

## 0.9

- Bouton « Récupérer la dernière version sur GitHub » : télécharge le firmware.bin de la dernière release du dépôt du plugin (firmware compilé sans aucun mot de passe).

## 0.8

- Configuration : présentation corrigée, mot de passe OTA masqué (bouton afficher) et bouton « Générer un nouveau mot de passe ».
- Chemin du firmware.bin corrigé dans l'aide.

## 0.7

- Appairage des nouveaux boutons : le bouton affiche un code, la demande apparaît sur la page du plugin (Accepter / Refuser), puis le plugin lui transmet broker, utilisateur MQTT (repris de MQTT Manager) et mot de passe de mise à jour (généré par le plugin). Nécessite le firmware 2.2-c.

## 0.6

- Correction : le firmware est servi par une page du plugin (le dossier data renvoyait une erreur 403).

## 0.5

- Mise à jour du firmware des boutons depuis Jeedom : dépôt du firmware.bin sur la page du plugin, bouton « Mettre à jour le firmware » et commande action du même nom, suivi dans « État de la mise à jour ». Nécessite le firmware 2.1 sur le bouton.

## 0.4

- État affiché sur la vignette de chaque bouton : En ligne / Hors ligne, version du firmware et source de la configuration.

## 0.3

- Nouvel onglet « Écrans du bouton » : éditeur par écran (Présence, Lumières, Volets, Actions groupées, Chauffage, Capteurs, Badges) avec le sélecteur de commandes Jeedom. Le JSON est généré automatiquement (onglet JSON).

## 0.2

- Correction : « La configuration est vide » à l'envoi (Jeedom convertit le JSON en tableau à la sauvegarde). Le bouton Envoyer utilise directement le contenu du champ.

## 0.1

- Détection automatique des boutons, commandes de supervision (en ligne, version, IP, signal Wi-Fi, mémoire…).
- Envoi de la configuration JSON au bouton et retour à la configuration locale.
