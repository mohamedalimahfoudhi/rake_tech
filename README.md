# RackeTech - Plateforme de Gestion Sportive

Une application web complète basée sur Symfony pour la gestion d'événements sportifs, de terrains, de billets, et de matériel sportif. Cette plateforme est conçue pour faciliter l'organisation de tournois, la réservation de terrains, et la gestion des équipements sportifs.

## Table des matières

- [Installation](#installation)
- [Fonctionnalités](#fonctionnalités)
- [Utilisation](#utilisation)
- [Structure du projet](#structure-du-projet)
- [Fonctionnalités récentes](#fonctionnalités-récentes)
- [Contribution](#contribution)
- [Licence](#licence)

## Installation

1. Clonez le repository :
   ```bash
   git clone https://github.com/votre-username/rake_tech-integration.git
   cd rake_tech-integration
   ```

2. Installez les dépendances avec Composer :
   ```bash
   composer install
   ```

3. Configurez la base de données dans le fichier `.env` :
   ```
   DATABASE_URL="mysql://username:password@localhost:3306/raketech_db?serverVersion=8.0"
   ```

4. Créez la base de données et exécutez les migrations :
   ```bash
   php bin/console doctrine:database:create
   php bin/console doctrine:migrations:migrate
   ```

5. Chargez les fixtures (données de démonstration) :
   ```bash
   php bin/console doctrine:fixtures:load
   ```

6. Démarrez le serveur de développement :
   ```bash
   symfony server:start
   ```
   ou
   ```bash
   php -S localhost:8000 -t public/
   ```

7. Accédez à l'application dans votre navigateur à l'adresse : `https://localhost:8000`

## Fonctionnalités

RackeTech offre une large gamme de fonctionnalités pour différents types d'utilisateurs :

### Pour les Administrateurs
- Gestion complète des utilisateurs et des droits d'accès
- Gestion du matériel sportif (ajout, modification, suppression)
- Création et organisation d'événements et de tournois
- Suivi des maintenances d'équipements
- Génération de rapports et statistiques
- Export PDF des billets, matériels et maintenances

### Pour les Athlètes
- Inscription aux événements et tournois
- Visualisation de billets avec QR code
- Réservation de terrains
- Location de matériel sportif
- Signalement de problèmes sur les équipements

### Pour les Organisateurs
- Création et gestion d'événements sportifs
- Suivi des inscriptions
- Gestion des terrains disponibles
- Génération de QR codes pour les entrées

## Utilisation

### Accès à l'application

L'application dispose de plusieurs interfaces selon le type d'utilisateur :

- **Espace administrateur** : `/admin/dashboard`
- **Espace athlète** : `/athlete/dashboard`
- **Espace organisateur** : `/organizer/dashboard`

### Gestion des billets

Pour gérer les billets d'entrée aux événements :
1. Accédez à la section `/admin/tickets`
2. Vous pouvez ajouter, modifier et supprimer des billets
3. Exportez la liste des billets en PDF avec le bouton "Export PDF"
4. Visualisez les QR codes associés aux billets

### Utilisation des exports PDF

L'application permet d'exporter plusieurs éléments en PDF :
- Liste des billets (accessible via `/admin/tickets/export`)
- Inventaire du matériel sportif
- Plannings de maintenance
- Informations sur les événements

## Structure du projet

Le projet suit l'architecture standard de Symfony avec quelques spécificités :

- `src/Controller/` - Contrôleurs de l'application
- `src/Entity/` - Entités Doctrine (modèles de données)
- `src/Form/` - Types de formulaires
- `src/Repository/` - Repositories Doctrine
- `templates/` - Templates Twig pour le rendu HTML
- `public/` - Ressources accessibles publiquement (CSS, JS, images)

## Fonctionnalités récentes

### Export PDF des billets

Nous avons récemment implémenté une fonctionnalité d'export PDF pour les billets, permettant aux administrateurs d'exporter la liste complète des billets dans un document PDF bien formaté.

#### Caractéristiques de l'export PDF
- **URL d'accès** : `/admin/tickets/export`
- **Bouton** : Disponible sur la page de gestion des billets
- **Filtres supportés** : Les filtres par type de billet et les conversions de devises sont pris en compte
- **Contenu** : Inclut les détails complets des billets, les informations sur les événements associés, et calcule la valeur totale des billets

#### Technologie utilisée
- [Dompdf](https://github.com/dompdf/dompdf) pour la génération de PDF
- Template Twig personnalisé pour le rendu du PDF

### Affichage des terrains

Amélioration de l'affichage des informations sur les terrains dans la vue des billets pour les athlètes, avec une meilleure présentation des données structurées.

### Pagination uniformisée

Implémentation d'une pagination cohérente sur l'ensemble des sections administratives grâce à KnpPaginatorBundle.

## Contribution

Les contributions à ce projet sont les bienvenues. Voici comment vous pouvez contribuer :

1. Fork du repository
2. Créez une branche pour votre fonctionnalité (`git checkout -b feature/nom-de-la-fonctionnalite`)
3. Committez vos changements (`git commit -am 'Ajout d'une nouvelle fonctionnalité'`)
4. Push vers la branche (`git push origin feature/nom-de-la-fonctionnalite`)
5. Créez une Pull Request

### Normes de codage

- Suivez les standards de codage PSR-12
- Documentez clairement vos fonctions et classes
- Écrivez des tests unitaires pour les nouvelles fonctionnalités
- Assurez-vous que tous les tests passent avant de soumettre une PR

## Licence

Ce projet est sous licence MIT. Voir le fichier LICENSE pour plus de détails.

---

Développé avec ❤️ par l'équipe RackeTech