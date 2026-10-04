# Plugin M5Dial

Le plugin **M5Dial** permet d'utiliser des boutons **M5Stack M5Dial** (écran rond, molette, lecteur RFID) comme télécommandes murales pour Jeedom.

Depuis Jeedom, vous pouvez :

- **superviser** chaque bouton (en ligne, version, signal Wi-Fi, mémoire…) ;
- **configurer ses écrans** (lumières, volets, chauffage, présence, capteurs, badges RFID) sans recompiler le firmware ;
- **appairer** un nouveau bouton en quelques secondes, sans mot de passe à saisir ;
- **mettre à jour le firmware** de tous les boutons, depuis Jeedom ou directement depuis GitHub.

Toute la communication passe par **MQTT**.

---

## 1. Prérequis

| Élément | Détail |
| --- | --- |
| Jeedom | 4.4 ou plus, Debian 11 ou plus |
| Plugin **MQTT Manager** (mqtt2) | installé, configuré et **démarré** : il fournit le broker et la connexion |
| Utilisateur MQTT | au moins un utilisateur déclaré dans MQTT Manager (champ *Authentification*, format `utilisateur:motdepasse`) |
| Bouton M5Dial | M5Stack Dial v1.1 avec le firmware M5Dial (2.2 ou plus pour l'appairage, 2.3 ou plus conseillé) |
| Wi-Fi | réseau **2,4 GHz**. Sur les bornes Wi-Fi 6 (802.11ax), activez si possible le mode de compatibilité Wi-Fi 5 sur la radio 2,4 GHz : en Wi-Fi 6 pur, le bouton peut perdre beaucoup de paquets (commandes lentes, déconnexions MQTT) |

> Conseil : réservez une adresse IP fixe (bail DHCP) pour chaque bouton sur votre box ou routeur.

---

## 2. Installation et configuration du plugin

1. Installez le plugin (dépôt GitHub `JeromeR60/jeedom-m5dial`, branche `main`), puis **activez-le**.
2. Ouvrez **Plugins → Gestion des plugins → M5Dial → Configuration**.

### Section « Général »

- **Topic racine des boutons** : `m5dial`. Les boutons publient sur `m5dial/<nom>/status` et `m5dial/<nom>/info` et reçoivent leur configuration sur `m5dial/<nom>/config`.
- Le plugin s'abonne automatiquement à ce topic auprès de MQTT Manager.

### Section « Identifiants transmis aux boutons lors de l'appairage »

- **Utilisateur MQTT des boutons** : choisissez un utilisateur de MQTT Manager. Son mot de passe est transmis automatiquement au bouton lors de l'appairage : vous n'avez jamais à le saisir.
- **Mot de passe des mises à jour (OTA)** : généré par le plugin à l'installation. L'icône en forme d'œil l'affiche. **Générer un nouveau mot de passe** en crée un autre, qui ne s'applique qu'aux boutons appairés **ensuite** (les autres gardent l'ancien jusqu'à un réappairage).

> Le mot de passe OTA ne sert qu'aux mises à jour depuis PlatformIO (développement). Les mises à jour depuis Jeedom n'en ont pas besoin.

---

## 3. Premier démarrage d'un bouton

### 3.1 Installer le firmware

La toute première installation se fait par câble USB depuis PlatformIO (environnement `m5dial_release` : firmware sans aucun mot de passe). Les mises à jour suivantes se font depuis Jeedom (voir §6).

### 3.2 Portail de configuration Wi-Fi

Au premier démarrage (aucun Wi-Fi enregistré), le bouton ouvre son **portail de configuration** :

1. L'écran affiche le nom d'un réseau Wi-Fi (`M5Dial-XXXX`), un mot de passe à 8 chiffres et un **QR code**.
2. Connectez votre téléphone ou ordinateur à ce réseau (en scannant le QR code ou manuellement).
3. Ouvrez **http://192.168.4.1** dans le navigateur.
4. Choisissez votre Wi-Fi dans la liste, saisissez son mot de passe et l'**adresse de Jeedom** (IP du broker MQTT), puis validez.

Le bouton redémarre et se connecte à votre réseau.

Le portail se rouvre aussi :

- si le Wi-Fi enregistré reste injoignable pendant 90 secondes ;
- si vous **maintenez le bouton appuyé au démarrage**, pour changer de Wi-Fi ou forcer un réappairage (section *Avancé*, case *Réappairer*).

> En cas d'échec de connexion depuis un iPhone, « oubliez » le réseau `M5Dial-XXXX` dans les réglages Wi-Fi puis réessayez, ou utilisez un ordinateur.

### 3.3 Appairage avec Jeedom

Si le bouton ne connaît pas encore ses identifiants MQTT, il passe en **appairage** :

1. L'écran du bouton affiche un **code à 4 chiffres**.
2. Dans Jeedom, ouvrez la page du plugin M5Dial : la demande apparaît dans **Nouveaux boutons en attente d'appairage** (nom, adresse MAC, adresse IP, firmware, code).
3. **Vérifiez que le code est identique** à celui affiché sur le bouton, puis cliquez sur **Accepter** (ou **Refuser**).
4. Le bouton reçoit l'utilisateur et le mot de passe MQTT ainsi que le mot de passe OTA, les enregistre dans sa mémoire et redémarre.

Quelques secondes plus tard, l'équipement est **créé automatiquement** dans Jeedom.

---

## 4. Les équipements

Chaque bouton apparaît dans **Mes boutons M5Dial** dès qu'il se connecte au broker. La vignette indique :

- l'**état** : En ligne (pastille verte), Hors ligne ou Jamais vu ;
- la **version du firmware** ;
- la **source de la configuration** (envoyée par Jeedom ou locale au bouton).

### Onglet « Equipement »

- Paramètres Jeedom habituels : nom, objet parent, catégorie, activer, visible.
- **Nom du bouton** : nom MQTT du bouton (ex. `m5dial-salon`), rempli automatiquement.
- **Identifiant MQTT** : nom suivi des 4 derniers caractères de l'adresse MAC (unique par bouton).
- **Mettre à jour le firmware** : voir §6.

### Commandes

| Commande | Type | Rôle |
| --- | --- | --- |
| En ligne | info binaire | 1 si le bouton est connecté (message de « dernière volonté » MQTT) |
| Version firmware | info texte | version installée sur le bouton |
| Adresse IP | info texte | adresse du bouton sur le réseau |
| Signal Wi-Fi | info numérique (dBm) | qualité du Wi-Fi (bonne au-dessus de −65 dBm) |
| Source de la configuration | info texte | Jeedom ou locale |
| Durée de fonctionnement | info numérique (min) | temps depuis le dernier redémarrage |
| Mémoire libre | info numérique (Ko) | mémoire disponible sur le bouton (historisée) |
| Dernière erreur de configuration | info texte | erreur renvoyée si la configuration reçue est invalide |
| État de la mise à jour | info texte | avancement d'une mise à jour du firmware |
| Envoyer la configuration | action | envoie la configuration de l'équipement au bouton |
| Revenir à la configuration locale | action | efface la configuration envoyée : le bouton reprend son `config.json` interne |
| Mettre à jour le firmware | action | lance la mise à jour avec le firmware disponible dans Jeedom |

Les informations sont rafraîchies toutes les 5 minutes et à chaque reconnexion du bouton.

---

## 5. Configurer les écrans d'un bouton

### Onglet « Écrans du bouton »

Cochez les écrans à afficher sur le bouton, puis choisissez les commandes Jeedom avec le sélecteur de commandes.

| Écran | Ce qu'on configure |
| --- | --- |
| **Présence** | mode actuel (Présent / Absent / Vacances), actions Présent et Absent, nombre de jours de vacances (info + curseur) |
| **Lumières** | une ligne par lumière : nom affiché, étage, On / Off / État et, en option, luminosité, température de blanc (mireds) et couleur |
| **Volets** | une ligne par volet : nom, étage, position (info et curseur), Monter / Descendre / Stop |
| **Actions groupées** | tout allumer / éteindre, ouvrir / fermer tous les volets ou par étage, heure de fermeture automatique |
| **Chauffage** | température ambiante, consigne (info, curseur, mini, maxi, pas), modes Confort / Nuit / Vacances / Off, statut de chauffe, puissance, température extérieure |
| **Capteurs** | une ligne par pièce : nom, température, humidité |
| **Badges RFID** | commande message appelée quand un badge est passé, et commande pour enregistrer un nouveau badge |

> Écrivez les noms affichés **sans accents** : la police du bouton ne les contient pas.

Ensuite :

1. **Sauvegardez** l'équipement.
2. Cliquez sur **Envoyer la configuration au bouton** : le bouton l'applique et redémarre sur ses nouveaux écrans.

Pour annuler, **Revenir à la configuration locale** rend au bouton son `config.json` d'origine.

### Onglet « JSON »

Il contient la configuration générée par l'onglet *Écrans du bouton*. Vous pouvez aussi la coller ou la modifier à la main. Sections reconnues : `presence`, `lumieres`, `volets`, `voletPositionFermee`, `groupes`, `chauffage`, `capteurs`, `badges`. La section `device` est ignorée (le bouton garde son nom et son broker). Taille maximale : **8 Ko**.

En cas d'erreur, le bouton garde sa configuration précédente et renseigne la commande *Dernière erreur de configuration*.

---

## 6. Mettre à jour le firmware des boutons

### 6.1 Rendre un firmware disponible dans Jeedom

Sur la page du plugin, section **Firmware des boutons** :

- **Récupérer la dernière version sur GitHub** (recommandé) : télécharge le `firmware.bin` de la dernière release publiée. Ces firmwares sont compilés **sans aucun mot de passe** ; chaque bouton garde ses propres réglages.
- ou **Déposer un firmware.bin** : envoie un fichier que vous avez compilé avec PlatformIO.

La ligne **Firmware disponible pour les mises à jour** affiche la version, la taille et la date du fichier présent dans Jeedom.

### 6.2 Mettre à jour un bouton

Dans l'équipement, cliquez sur **Mettre à jour le firmware** (ou utilisez la commande action du même nom, par exemple dans un scénario).

Le bouton télécharge le firmware depuis Jeedom, l'installe puis redémarre (environ 30 secondes). La commande **État de la mise à jour** suit l'avancement (pourcentage, `ok, redemarrage` ou message d'erreur). En cas de coupure, le bouton fait jusqu'à 3 tentatives.

La version affichée sur la vignette change dès que le bouton est de retour en ligne.

---

## 7. Topics MQTT (pour information)

| Topic | Sens | Contenu |
| --- | --- | --- |
| `m5dial/<nom>/status` | bouton → Jeedom | `online` / `offline` (retenu, message de dernière volonté) |
| `m5dial/<nom>/info` | bouton → Jeedom | JSON : nom, clientId, version, config, ip, mac, rssi, ssid, uptimeMin, memoireLibreKo (retenu) |
| `m5dial/<nom>/config` | Jeedom → bouton | configuration JSON (retenu) ; message vide = configuration locale |
| `m5dial/<nom>/config/erreur` | bouton → Jeedom | erreur de configuration |
| `m5dial/<nom>/ota` | Jeedom → bouton | `{"url": ..., "version": ...}` : demande de mise à jour |
| `m5dial/<nom>/ota/etat` | bouton → Jeedom | avancement de la mise à jour |

---

## 8. Dépannage

| Symptôme | Que faire |
| --- | --- |
| Le bouton n'apparaît pas dans Jeedom | Vérifier que MQTT Manager est démarré, que le bouton est connecté au Wi-Fi et qu'il a été appairé. Avec MQTT Explorer, vérifier qu'il publie sur `m5dial/#`. |
| Aucune demande d'appairage | Vérifier l'adresse de Jeedom saisie dans le portail ; rouvrir le portail (bouton maintenu au démarrage) et cocher *Réappairer*. |
| Commandes lentes, déconnexions MQTT | Lancer un `ping -t` vers le bouton et vers Jeedom : si seul le bouton perd des paquets, le problème vient du Wi-Fi (Wi-Fi 6 sur 2,4 GHz, signal faible). |
| « La configuration est vide » | Sauvegarder l'équipement avant d'envoyer la configuration. |
| Mise à jour en échec | Vérifier qu'un firmware est disponible (§6.1) et que le bouton est en ligne, puis relancer. |
| Les accents s'affichent mal | Écrire les noms sans accents. |

---

## 9. Sécurité

- Aucun mot de passe n'est contenu dans les firmwares publiés : le Wi-Fi est saisi dans le portail du bouton, les identifiants MQTT et OTA sont transmis par Jeedom lors de l'appairage et stockés dans la mémoire du bouton.
- L'appairage demande une validation manuelle dans Jeedom, avec vérification du code affiché.
- Le bouton n'accepte que les mises à jour téléchargées depuis l'adresse de Jeedom qu'il connaît.
