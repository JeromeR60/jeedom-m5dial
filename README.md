# M5Dial pour Jeedom

Plugin Jeedom et firmware qui transforment un **M5Stack Dial** (écran rond, molette, lecteur RFID) en télécommande murale pour Jeedom.

<img src="docs/images/bouton-accueil.jpg" alt="Bouton M5Dial" width="300">

## Fonctions

- Lumières (on/off, intensité, blanc, couleur), volets, chauffage, modes de présence
- Badges RFID, capteurs (température, humidité), météo avec prévisions sur deux jours
- Écrans configurés depuis Jeedom, sans recompiler le firmware
- Appairage d'un nouveau bouton en quelques secondes, sans mot de passe à saisir
- Mises à jour du firmware par Wi-Fi depuis Jeedom (notification des nouvelles versions)

Toute la communication passe par MQTT (plugin **MQTT Manager**).

## Installation

1. **Plugin** : dans Jeedom, ajoutez le plugin depuis GitHub (utilisateur `JeromeR60`, dépôt `jeedom-m5dial`, branche `main`, ID logique `m5dial`). Détails au §2 de la documentation.
2. **Firmware du bouton** : depuis Chrome ou Edge, page d'installation web : **https://jeromer60.github.io/jeedom-m5dial/installer/**
3. **Appairage** : le bouton affiche un code, acceptez-le sur la page du plugin.

## Documentation

- [Documentation complète](docs/fr_FR/index.md)
- [Changelog](docs/fr_FR/changelog.md)
- [Guide du bêta-testeur](docs/fr_FR/beta-testeurs.md) (branche `beta`)

Questions et problèmes : onglet [Issues](https://github.com/JeromeR60/jeedom-m5dial/issues).

## Prérequis

Jeedom 4.4 ou plus, MQTT Manager démarré, M5Stack Dial v1.1, Wi-Fi 2,4 GHz.

---

Projet indépendant, non affilié à M5Stack ni à Jeedom. Licence AGPL.
