# Changelog plugin M5Dial

## 1.12

- Écran Distributeur : avec planningDistrib 0.1.6 ou plus récent (info « Manuel aujourd'hui »), le compteur du bouton n'affiche plus que les doses du planning (« 10 / 19 ») et les distributions manuelles à part (« +1 manuelle »). Le total ne dépasse donc plus les doses prévues. Nécessite le firmware 3.3.
- Documentation : nouvelle section **§5.4 Page Planning du chauffage**, présentation des plugins de planning facultatifs (planningThermo, planningDistrib), lignes de dépannage, README.

## 1.11

- Écran Distributeur : champ **Planning (planningDistrib)** pour chaque distributeur, liste des équipements du plugin planningDistrib. Le bouton affiche alors une vue d'ensemble : planning du jour sur un anneau de 24 heures, doses distribuées / prévues aujourd'hui, prochaine et dernière distribution. La distribution manuelle passe par le plugin planningDistrib (comptée dans « Distribué aujourd'hui »). Bornes de dose, couleur et nom (s'il est vide) sont repris du planning à chaque envoi de la configuration. Nécessite le firmware 3.2.
- Firmware 3.2 : distribution en **deux appuis** (« Distribuer 3 ? », second appui dans les 3 secondes), pour tous les distributeurs.

## 1.10

- Écran Chauffage : nouvelle **page Planning** (swipe vers la gauche depuis le thermostat), pour les thermostats pilotés par le plugin **planningThermo**. Le bouton affiche le planning du jour sur un anneau de 48 créneaux, le mode en cours, son heure de fin et le mode suivant. Il permet de créer une dérogation (mode et durée : jusqu'au prochain changement, 30 min, 1 h, 2 h, 3 h), de l'annuler, et de suspendre ou reprendre le planning. Quand le thermostat est verrouillé (vacances), la page l'indique. Nécessite le firmware 3.1.
- Section Chauffage : champ **Planning (page supplémentaire)**, liste des équipements planningThermo. Il suffit de choisir l'équipement : les commandes, l'info de verrouillage du thermostat et les modes avec leurs couleurs sont repris automatiquement à chaque envoi de la configuration. Sans plugin planningThermo, l'écran Chauffage reste tel quel.
- « Importer depuis un thermostat » choisit aussi le planning qui pilote ce thermostat, s'il y en a un.

## 1.9

- Écran Chauffage : nouveau **mode Eco** (champ « Mode Eco » dans la section Chauffage). Sur le bouton, seuls les modes renseignés sont affichés et proposés à l'appui : un thermostat sans Eco (ou sans Vacances) n'a plus d'icône inutile. Nécessite le firmware 3.0.
- Section Chauffage : bouton **Importer depuis un thermostat**. Il remplit automatiquement les commandes à partir d'un équipement du plugin Thermostat (température, consigne, mode, statut, puissance, température extérieure, consigne mini / maxi et les modes Confort, Eco, Nuit, Vacances / Hors-gel, Off). Les champs non trouvés sont laissés tels quels et signalés.

## 1.8

- « Mettre à jour le firmware » récupère d'abord la dernière version publiée sur GitHub si elle est plus récente que celle déposée dans Jeedom : plus besoin de cliquer avant sur « Récupérer la dernière version sur GitHub ». La commande peut donc être placée telle quelle dans un virtuel ou un scénario. Un firmware déposé à la main plus récent (mode avancé) est conservé ; si GitHub est injoignable, le firmware déjà déposé est envoyé.
- Documentation : exemple de bouton « Mettre à jour » dans un virtuel, affiché par scénario seulement quand une version est disponible (§6.4) ; lecture des courbes de mémoire (§8).

## 1.7

- Deux nouvelles commandes de supervision, historisées : **Mémoire mini** (point le plus bas atteint depuis le démarrage du bouton) et **Plus gros bloc libre** (plus grand bloc de mémoire réservable d'un coup, révèle la fragmentation). Nécessite le firmware 2.9.
- La **Durée de fonctionnement** est historisée sur les nouveaux équipements (permet de repérer les redémarrages sur un graphique).

## 1.6

- Nouvel écran **Distributeur** (croquettes…) : choix du nombre de doses à la molette, distribution d'un appui, état de connexion (distribution bloquée hors ligne). Nécessite le firmware 2.8.

## 1.5

- Écran Météo : prévisions de demain et après-demain (condition, min, max), affichées en tournant la molette. Nécessite le firmware 2.7.
- La version du plugin est affichée dans la configuration du plugin (une installation depuis GitHub n'affiche que le numéro de commit dans Jeedom).

## 1.4

- Nouvel écran **Météo** dans l'éditeur : température, humidité, icône de la condition (jour / nuit), min / max. Chaque champ peut venir du plugin Météo ou d'une sonde extérieure. Nécessite le firmware 2.6.
- Nouveau cadre **Menu du bouton** : choix de l'ordre des écrans sur le bouton (flèches). Nécessite le firmware 2.6.

## 1.3.2

- Documentation : exemple complet badges RFID et présence (virtuel, variable, scénarios).

## 1.3.1

- Documentation : captures pas à pas de l'installation d'un bouton neuf.

## 1.3

- Nouvelle icône du plugin au format du Market (309 × 348) ; les vignettes gardent l'image du bouton.
- Langue déclarée : français uniquement (pas encore de traduction anglaise).

## 1.2
- Sécurité : la mise à jour du firmware utilise un jeton à usage limité (15 min) au lieu de la clé API du plugin.
- Sécurité : les identifiants d'appairage ne sont remis qu'à l'adresse IP qui a fait la demande acceptée.
- Sécurité : page de configuration du plugin réservée aux administrateurs.
- Documentation : section « Sécurité et données » détaillée.

## 1.1

- Page d'installation web d'un M5Dial neuf (Chrome / Edge, câble USB-C), avec le firmware 2.4.

## 1.0.1

- Documentation : nouvelles captures (configuration, fonctionnalités, firmware).

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
