# Plugin M5Dial — Guide du bêta-testeur

Merci de tester le plugin **M5Dial** ! Il transforme un bouton **M5Stack Dial** (écran rond, molette, lecteur RFID) en télécommande murale pour Jeedom : lumières, volets, chauffage, présence, capteurs et badges RFID.

Le plugin n'est pas encore sur le Market Jeedom : il s'installe depuis GitHub.

---

## 1. Ce qu'il vous faut

- **Jeedom 4.4** ou plus (Debian 11 ou plus).
- Le plugin **MQTT Manager** (mqtt2) installé, configuré et **démarré**, avec au moins un utilisateur MQTT (champ *Authentification* de MQTT Manager, format `utilisateur:motdepasse`). Idéalement, créez un utilisateur dédié aux boutons, par exemple `m5dial`.
- Un **M5Stack Dial** (v1.1) et un câble **USB-C de données** (certains câbles ne font que la charge).
- Un ordinateur avec **Chrome** ou **Edge** pour installer le firmware du bouton.
- Un **Wi-Fi 2,4 GHz**. Sur les bornes Wi-Fi 6, activez si possible le mode de compatibilité Wi-Fi 5 sur la radio 2,4 GHz (sinon : commandes lentes, déconnexions).

---

## 2. Installer le plugin depuis GitHub

**a. Autoriser GitHub comme source** (une seule fois)

Jeedom → **Réglages → Système → Configuration** → onglet **Mises à jour/Market** → sous-onglet **GitHub** : cochez **Activer GitHub**, puis **Sauvegarder**. Le jeton (token) n'est pas nécessaire.

**b. Ajouter le plugin**

Jeedom → **Plugins → Gestion des plugins** → bouton **+** (Ajouter un plugin) → type de source **GitHub** :

| Champ | Valeur |
| --- | --- |
| ID logique du plugin | `m5dial` |
| Utilisateur ou organisation du dépôt | `JeromeR60` |
| Nom du dépôt | `jeedom-m5dial` |
| Branche | `beta` |

**Enregistrer**, puis **activer** le plugin.

**c. Configurer le plugin**

Dans la configuration du plugin, choisissez l'**utilisateur MQTT des boutons**, puis sauvegardez. Vérifiez aussi, dans **Fonctionnalités**, que **cronDaily** est sur **Oui** (vérification des nouvelles versions du firmware).

---

## 3. Installer le bouton

1. Ouvrez la page d'installation dans Chrome ou Edge : **https://jeromer60.github.io/jeedom-m5dial/installer/**
2. Branchez le M5Dial en USB-C, cliquez sur **Installer**, choisissez le port (souvent « USB JTAG/serial debug unit »), laissez **Erase device** coché et confirmez. Comptez 1 à 2 minutes.
3. Le bouton redémarre sur son **portail de configuration** : connectez-vous au Wi-Fi `M5Dial-XXXX` (mot de passe affiché sur l'écran, ou QR code), puis ouvrez **http://192.168.4.1**. Choisissez votre Wi-Fi, saisissez son mot de passe et l'**adresse IP de Jeedom**, donnez un nom au bouton (ex. `m5dial-salon`), puis **Enregistrer**.
4. Le bouton affiche un **code à 4 chiffres** : dans Jeedom, page du plugin M5Dial, vérifiez le code et cliquez sur **Accepter**.
5. L'équipement apparaît dans Jeedom. Configurez ses écrans dans l'onglet **Écrans du bouton**, sauvegardez, puis **Envoyer la configuration au bouton**.

La documentation complète, avec captures d'écran :
**https://github.com/JeromeR60/jeedom-m5dial/blob/main/docs/fr_FR/index.md**

---

## 4. Ce qui m'intéresse dans vos retours

- L'installation : plugin, page d'installation du bouton, portail Wi-Fi, appairage. Tout ce qui a bloqué ou n'était pas clair.
- La configuration des écrans dans Jeedom : facile ou non, ce qui manque.
- L'utilisation au quotidien : réactivité, déconnexions, écrans qui ne se mettent pas à jour.
- Les mises à jour du firmware depuis Jeedom.
- La documentation : passages à préciser, erreurs.

**Pour un problème, merci de joindre si possible :**

- la version du plugin et du firmware (vignette de l'équipement) ;
- le log du plugin (**Analyse → Logs → m5dial**, en niveau *Debug* si possible) ;
- une photo de l'écran du bouton ou une capture de Jeedom ;
- votre modèle de borne Wi-Fi.

⚠️ Avant d'envoyer un log ou une capture, vérifiez qu'aucun **mot de passe** n'y figure.

**Où envoyer vos retours :** de préférence dans l'onglet **Issues** du dépôt GitHub (https://github.com/JeromeR60/jeedom-m5dial/issues), sinon directement à Jérôme.

---

## 5. Bon à savoir

- C'est une **version bêta** : des bugs sont possibles. Vous recevrez les nouvelles versions par la mise à jour habituelle des plugins Jeedom.
- Le firmware du bouton se met à jour **depuis Jeedom** (page du plugin → « Récupérer la dernière version sur GitHub », puis « Mettre à jour le firmware » dans l'équipement). Un badge **MAJ dispo** apparaît sur la vignette quand une nouvelle version est publiée.
- Les noms affichés sur le bouton doivent être **sans accents** (la police du bouton ne les contient pas).
- Quand le plugin sera publié sur le Market Jeedom, je vous indiquerai comment passer de la version GitHub à la version Market.
- Aucun mot de passe n'est contenu dans le firmware publié : le Wi-Fi est saisi dans le portail du bouton, les identifiants MQTT sont transmis par Jeedom lors de l'appairage, après votre validation.

Plugin indépendant, non affilié à M5Stack. Licence AGPL.

Merci pour votre aide !
