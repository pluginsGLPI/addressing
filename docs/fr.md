# Plugin Addressing pour GLPI — Documentation française

## Présentation

Le plugin **Addressing** permet de gérer et visualiser des plages d'adresses IP dans GLPI.
Il génère des rapports indiquant, pour chaque IP d'un réseau donné, si elle est attribuée, libre, réservée ou en doublon. Il offre également la réservation d'IP, le ping automatique et l'export des rapports.

- **Licence :** GPLv3+
- **Dépôt :** <https://github.com/pluginsGLPI/addressing>

---

## Installation

1. Copiez le dossier `addressing` dans le répertoire `marketplace/` de votre instance GLPI.
2. Rendez-vous dans **Configuration → Plugins** et cliquez sur **Installer** puis **Activer**.

L'installation crée automatiquement les tables SQL nécessaires et enregistre une tâche planifiée (`UpdatePing`).

---

## Désinstallation

Dans **Configuration → Plugins**, cliquez sur **Désinstaller**.
Toutes les tables du plugin et les droits associés sont supprimés.

---

## Droits

Le plugin ajoute un onglet **IP Addressing** dans la page **Administration → Profils**.

| Droit | Description |
|---|---|
| `plugin_addressing` | Lire, créer, modifier, supprimer des plages IP (READ / CREATE / UPDATE / DELETE / PURGE) |
| `plugin_addressing_use_ping_in_equipment` | Afficher le résultat de ping dans la fiche d'un équipement |

---

## Configuration

Accédez à la page de configuration via **Outils → IP Addressing → (icône paramètres)** ou
**Configuration → Plugins → Addressing → Configurer**.

| Paramètre | Description |
|---|---|
| **Afficher les IP attribuées** | Inclure les IP déjà assignées à un équipement dans le rapport |
| **Afficher les IP libres** | Inclure les IP sans aucun équipement associé |
| **Afficher les IP réservées** | Inclure les IP réservées (port `reserv-*`) |
| **Afficher les doublons** | Inclure les IP présentes sur plusieurs équipements |
| **Utiliser le ping** | Activer la fonction ping pour détecter les IP libres actives |
| **Système de ping** | Choisir le système d'exploitation utilisé pour le ping |

> **Remarque :** Sur GLPI Cloud, la fonction ping est automatiquement désactivée.

---

## Utilisation

### Accès au module

Le module est accessible via **Outils → IP Addressing** pour les profils ayant le droit `plugin_addressing` ≥ READ.

### Créer une plage IP

1. Cliquez sur **Ajouter**.
2. Renseignez les champs :
   - **Nom** : nom descriptif de la plage (ex. : `Réseau bureau`)
   - **Première IP** / **Dernière IP** : bornes de la plage (ex. : `192.168.1.1` / `192.168.1.254`)
   - **Réseau** *(optionnel)* : lien vers un réseau GLPI
   - **Localisation** *(optionnel)* : localisation GLPI associée
   - **FQDN** *(optionnel)* : domaine DNS associé
   - **VLAN** *(optionnel)* : VLAN associé
   - **Afficher les IP attribuées / libres / réservées / doublons** : cases à cocher pour filtrer l'affichage par défaut du rapport
   - **Utiliser le ping** : activez cette option pour que les IP libres soient pingées automatiquement

3. Cliquez sur **Sauvegarder**.

### Générer un rapport

Ouvrez une plage IP existante et cliquez sur l'onglet **Rapport**.

Le tableau liste chaque IP de la plage avec :

| Colonne | Contenu |
|---|---|
| Adresse IP | IP numérotée dans la plage |
| Équipement | Nom de l'équipement associé (lien cliquable) |
| Port réseau | Nom du port réseau |
| MAC | Adresse MAC |
| Utilisateur | Utilisateur affecté à l'équipement |
| Type | Type d'équipement (Ordinateur, Imprimante, etc.) |

**Légende des couleurs :**

| Couleur | Signification |
|---|---|
| Fond normal | IP attribuée |
| Fond vert clair | IP libre (sans équipement) |
| Fond orange | IP réservée |
| Fond rouge | IP en doublon |
| Icône verte ✓ | Ping réussi (IP active) |
| Icône rouge ✗ | Ping sans réponse (IP inactive) |

### Filtres de rapport

L'onglet **Filtres** de chaque plage permet de créer des sous-plages pour restreindre l'affichage du rapport. Chaque filtre possède :

- **Nom**
- **Entité** : limite le rapport aux équipements de cette entité
- **Type** : limite à un type d'équipement spécifique
- **Première IP** / **Dernière IP** : sous-plage

Dans l'onglet Rapport, sélectionnez le filtre souhaité dans la liste déroulante puis cliquez sur **Rechercher**.

### Réservation d'IP

Depuis l'onglet Rapport, cliquez sur **Réserver** en face d'une IP libre pour réserver cette adresse.
Le formulaire demande :

| Champ | Description |
|---|---|
| **Nom de l'objet** | Nom de l'équipement à créer ou existant |
| **Type** | Type d'équipement (Ordinateur, Équipement réseau…) |
| **Entité** | Entité GLPI |
| **Localisation** | Localisation GLPI *(optionnel)* |
| **État** | État GLPI *(optionnel)* |
| **FQDN** | Domaine DNS *(optionnel)* |
| **MAC** | Adresse MAC *(optionnel)* |
| **Commentaire** | Commentaire libre |

Un port réseau nommé `reserv-<IP>` est automatiquement créé sur l'équipement.

### Commentaires sur les IP

Il est possible d'ajouter un commentaire texte sur n'importe quelle IP du rapport.
Ces commentaires sont stockés dans la table `glpi_plugin_addressing_ipcomments`.

### Ping dans la fiche équipement

Si le droit `plugin_addressing_use_ping_in_equipment` est activé pour le profil, un bloc **Ping** apparaît dans le formulaire de chaque équipement (Ordinateur, Imprimante, etc.).

Il affiche le dernier résultat connu (OK / KO) ainsi que la date du dernier test.

---

## Tâche planifiée (CRON)

Le plugin enregistre la tâche `UpdatePing` (fréquence : quotidienne).

Elle parcourt toutes les plages IP ayant l'option **Utiliser le ping** activée et met à jour les résultats dans la table `glpi_plugin_addressing_pinginfos`.

Vous pouvez aussi déclencher le ping manuellement depuis l'onglet Rapport via le bouton **Lancer le ping manuellement**.

---

## Export du rapport

Depuis l'onglet Rapport, utilisez les liens d'export en haut du tableau pagineur pour exporter au format :

- **PDF**
- **CSV**
- **SLK** (tableur)

---

## Actions massives

Les utilisateurs ayant le droit `UPDATE` peuvent utiliser les actions massives sur la liste des plages IP. L'action disponible est :

- **Transférer** : déplacer les plages sélectionnées vers une autre entité GLPI.

---

## Intégration DataInjection

Le plugin s'intègre avec le plugin **Data Injection** pour permettre l'import de plages IP en masse via fichier CSV.

---

## Tables de la base de données

| Table | Description |
|---|---|
| `glpi_plugin_addressing_addressings` | Plages IP |
| `glpi_plugin_addressing_filters` | Filtres associés aux plages |
| `glpi_plugin_addressing_pinginfos` | Résultats de ping par IP |
| `glpi_plugin_addressing_configs` | Configuration globale du plugin |
| `glpi_plugin_addressing_ipcomments` | Commentaires sur les IP |

---

## Options de recherche globale

Le plugin ajoute l'option de recherche **Résultat ping** sur tous les types d'équipements compatibles (Ordinateurs, Équipements réseau, Périphériques, Téléphones, Imprimantes, Baies, PDU, Clusters).
Cette option est disponible dans le moteur de recherche standard GLPI sous l'identifiant `5000`.
