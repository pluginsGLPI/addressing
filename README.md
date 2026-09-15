## Addressing plugin for GLPI

[![License](https://img.shields.io/badge/License-GNU%20GPL%20v3-blue.svg?style=flat-square)](https://github.com/pluginsGLPI/addressing/blob/master/LICENSE)
[![Translate](https://img.shields.io/badge/Translate-Transifex-cyan)](https://explore.transifex.com/pluginsGLPI/glpi-plugin-addressing/)

## Français

Ce plugin vous permet créer des rapports IP afin de visualiser les adresses ip utilisées et libres sur un réseau donné.
> * Génération de la liste des ip attribuées / libres / réservées / doublons pour tout type de matériel ayant une IP.
> * Réservations d'IP.
> * Filtre par réseau.
> * Ping adresses libres sur divers systèmes.
> * Export du rapport en pdf, csv, slk

> [!IMPORTANT]
> Le ping est conditionné par deux réglages : l'option globale « Utiliser Ping » de la configuration du plugin **et** l'option « Utiliser Ping » de la plage. L'option globale est désormais réellement appliquée : lorsqu'elle est désactivée, aucune plage n'est scannée, ni par la tâche automatique ni par le lancement manuel.

📖 [Documentation complète en français](docs/fr.md)

## English

This plugin enables you to create IP reports for visualize IP addresses used and free on a given network.
> * List generation of ip assigned / free / reserved / doubles for all items with IP field.
> * IP reservations.
> * Filter with network.
> * Ping fonction for free ip for many systems.
> * Report export to pdf, csv, slk

> [!IMPORTANT]
> Ping is gated by two settings: the global "Use Ping" option of the plugin configuration **and** the "Use Ping" option of the range. The global option is now actually enforced: when it is off, no range is scanned, neither by the cron task nor by the manual launch.

📖 [Full documentation in English](docs/en.md)
