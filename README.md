# SAé 23 – Mettre en place une solution informatique pour l’entreprise

Projet de deuxième semestre du BUT Réseaux & Télécommunications - IUT de Blagnac  
Réalisé par : **Timeo Champigny, Sarah Perez, Oihan Martin dit-Neuville, Hugo Diniz**  
Encadrant : M. Massaoudi  
📅 Année BUT1 2024-2025


## 📌 Contexte du projet

Dans le cadre de la SAÉ 23, nous avons été chargés de concevoir une **solution de supervision réseau** pour une infrastructure simulée.  
Le but est de **faciliter la gestion des équipements** et **centraliser les informations techniques** dans une interface claire et fonctionnelle.


## 🎯 Objectifs

- Mettre en place une chaîne de traitement via des conteneurs.
- Créer un dashboard Grafana complet.
- Coder un site web dynamique hébergé sur un serveur lampp.
- Coder un script récupérant les données sur le bus MQTT (langage au choix : bash, php, C, python,…).
- Créer et gérer une base de données MySQL.
- Automatiser la chaîne de traitement (scripts dans crontab)


## 🧰 Technologies utilisées

| Composant       | Description                                  |
|------------------|---------------------------------------------|
| Lubuntu 22.04 LTS       | Système d'exploitation pour la VM           |
| Apache        | Serveur Web                                 |
| MariaDB       | Base de données relationnelle               |
| PHP           | Langage côté serveur                        |
| Node-RED      | Outil de programmation visuelle (Flow)    |
| Grafana       | Outil de visualisation de données    |
| MQTT      | Protocole basé sur publish/subscribe    |
| FusionInventory | Plugin d'inventaire automatique pour GLPI |
| GNS3          | Simulation du réseau virtuel                |
| VMWare   | Outils de virtualisation pour la VM               |


## ⚙️ Fonctionnalités principales

- Interface Web GLPI personnalisée
- Détection automatique du matériel via FusionInventory
- Visualisation des statistiques réseau
- Système d’authentification multi-profils (admin, gestionnaire, utilisateur)
- Planification automatique des tâches d’inventaire
- Scripts Bash pour l'automatisation (crontab)


## 🧪 Déploiement local

### Prérequis
- VMWare Workstation Pro (Recommendé) ou VMWare Player 17
- VM Lubuntu 22.40 LTS
- Serveur LAMP (Apache, MariaDB, PHP)

### Installation

```bash
# Cloner ce dépôt
git clone https://github.com/<user>/<repo>.git

# Lancer les scripts d’installation
cd scripts/
bash install_lamp.sh
bash install_glpi.sh

