# SAé 23 – Mettre en place une solution informatique pour l’entreprise

Projet de deuxième semestre du BUT Réseaux & Télécommunications - IUT de Blagnac  
Réalisé par : **Timeo Champigny, Sarah Perez, Oihan Martin dit-Neuville, Hugo Diniz**  
Encadrant : M. Massaoudi  
📅 Année BUT1 2024-2025


## 📌 Contexte du projet

Dans le cadre de la SAÉ 23, nous avons été chargés de concevoir une **solution de supervision réseau** pour une infrastructure simulée.  
Le but est de **faciliter la gestion des équipements** et **centraliser les informations techniques** dans une interface claire et fonctionnelle.


## 🎯 Objectifs

- Déployer une solution de supervision complète et fonctionnelle
- Assurer un **inventaire automatique** du matériel via un agent
- Mettre en place un **dashboard de visualisation** des métriques réseau
- Gérer les **utilisateurs et leurs accès** à la plateforme
- Offrir une **interface Web dynamique** pour la consultation


## 🧰 Technologies utilisées

| Composant       | Description                                  |
|------------------|---------------------------------------------|
| Debian        | Système d'exploitation pour la VM           |
| Apache        | Serveur Web                                 |
| MariaDB       | Base de données relationnelle               |
| PHP           | Langage côté serveur                        |
| 🛠GLPI          | Solution de gestion d'infrastructure IT     |
| FusionInventory | Plugin d'inventaire automatique pour GLPI |
| GNS3          | Simulation du réseau virtuel                |
| VirtualBox    | Virtualisation des machines                 |


## ⚙️ Fonctionnalités principales

- 📋 Interface Web GLPI personnalisée
- 🖥️ Détection automatique du matériel via FusionInventory
- 📈 Visualisation des statistiques réseau
- 🔐 Système d’authentification multi-profils (admin, gestionnaire, utilisateur)
- ⏰ Planification automatique des tâches d’inventaire
- 🔄 Scripts Bash pour l'automatisation (crontab)


## 📂 Arborescence du projet

📁 SAE23/
├── 📁 docs/                     # Documentation du projet
│   ├── gantt.pdf               # Diagramme de GANTT
│   ├── schema_bd.png           # Schéma de la base de données
│   ├── captures/               # Captures d'écran (site, dashboard, etc.)
│   └── rapport_final.pdf       # Rapport ou synthèse de projet
│
├── 📁 glpi/                     # Dossiers liés à GLPI
│   ├── plugin/                 # Plugin FusionInventory ou autres
│   └── config/                 # Fichiers de config (si modifiés)
│
├── 📁 scripts/                  # Scripts d'automatisation
│   ├── install_lamp.sh         # Installation de la stack LAMP
│   ├── install_glpi.sh         # Installation de GLPI
│   └── cron_tasks.sh           # Tâches planifiées pour automatisation
│
├── 📁 sql/                      # Base de données
│   ├── init_db.sql             # Script de création des tables
│   └── insert_data.sql         # Données d’exemple si besoin
│
├── 📁 www/                      # Dossier du site web dynamique
│   ├── index.php               # Page d’accueil
│   ├── admin.php               # Interface administrateur
│   ├── gestionnaire.php        # Interface gestionnaire bâtiment
│   ├── consultation.php        # Accès libre aux mesures
│   └── assets/                 # CSS, JS, images
│
├── 📁 mqtt/                     # Scripts MQTT (si utilisés)
│   ├── subscriber.py           # Récupération des mesures
│   └── publisher.py            # Simulations de capteurs
│
├── 📁 docker/                   # Fichiers pour les conteneurs (optionnel)
│   └── docker-compose.yml      # Stack complète (Mosquitto, InfluxDB, etc.)
│
├── .gitignore                  # Fichiers/dossiers à ignorer par Git
├── README.md                   # Description complète du projet
└── LICENSE                     # Licence (optionnelle)



## 🧪 Déploiement local

### Prérequis
- VirtualBox
- VM Debian 11
- Serveur LAMP (Apache, MariaDB, PHP)
- Accès root

### Installation

```bash
# Cloner ce dépôt
git clone https://github.com/<user>/<repo>.git

# Lancer les scripts d’installation
cd scripts/
bash install_lamp.sh
bash install_glpi.sh

