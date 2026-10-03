# Plugin M5Dial

Supervision et configuration des télécommandes murales **M5Stack M5Dial** (firmware maison) par MQTT.

## Prérequis

- Plugin **MQTT Manager** (mqtt2) installé et démarré.
- Boutons M5Dial en firmware 2.0 ou plus, connectés au même broker.

## Fonctionnement

- Chaque bouton est créé automatiquement dès qu'il publie sur `m5dial/<nom>/status` ou `m5dial/<nom>/info`.
- Commandes info : En ligne, Version firmware, Adresse IP, Signal Wi-Fi, Source de la configuration, Durée de fonctionnement, Mémoire libre, Dernière erreur de configuration.
- Commandes action : **Envoyer la configuration** (publie le JSON de l'équipement, retenu, sur `m5dial/<nom>/config`) et **Revenir à la configuration locale** (message vide retenu).

## Configuration d'un bouton

Dans l'équipement, le champ « Configuration du bouton (JSON) » reprend les sections du `config.json` du bouton : `presence`, `lumieres`, `volets`, `voletPositionFermee`, `groupes`, `chauffage`, `capteurs`, `badges`. La section `device` est ignorée (le bouton garde son nom et son broker). Taille maximale : 8 Ko.
