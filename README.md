# CityAlert — Plateforme de signalement citoyen

Application web **PHP orientée objet, sans framework**, architecture **MVC** construite à la main.
Les citoyens signalent des incidents (voirie, éclairage, déchets, eau) ; les agents municipaux
les traitent ; l'administrateur consulte les statistiques.

> Cette livraison correspond à la **conception de l'application** : le socle technique complet
> et le cœur fonctionnel (auth, CRUD signalements, cycle de vie, catégories polymorphes,
> commentaires, tableau de bord, statistiques). Les options bonus (upload d'images, e-mail réel,
> API REST, carte Leaflet) sont laissées en extension.

## Installation

1. **Base de données** (MySQL 8+) :
   ```bash
   mysql -u root -p < database/schema.sql
   mysql -u root -p < database/seed.sql
   ```
2. **Configuration** : adapter `config/config.php` (identifiants MySQL) si besoin.
3. **Serveur** : faire pointer le serveur web (Apache/XAMPP) sur le dossier `public/`,
   ou en local :
   ```bash
   php -S localhost:8000 -t public
   ```
4. Ouvrir <http://localhost:8000>.

## Comptes de test (mot de passe : `password`)

| Rôle           | E-mail                |
|----------------|-----------------------|
| Administrateur | admin@cityalert.sn    |
| Agent          | agent@cityalert.sn    |
| Citoyen        | awa@cityalert.sn      |
| Citoyen        | modou@cityalert.sn    |

## Architecture (concepts POO démontrés)

```
app/
├── Core/          Router, Database (PDO), Request, Response, View, Session
├── Controllers/   AbstractController + Auth / Report / Comment / Dashboard / Admin
├── Models/
│   ├── Entities/  AbstractEntity (abstraite) + User, Report, Comment, StatusHistory
│   └── Categories/ AbstractCategory + 4 sous-classes + CategoryFactory (POLYMORPHISME)
├── Repositories/  RepositoryInterface + AbstractRepository + User/Report/Comment (PDO préparé)
├── Interfaces/    NotifiableInterface
├── Services/      AuthService, NotificationService (implémente NotifiableInterface)
├── Traits/        Timestampable (dates création/maj)
├── Enums/         Role, ReportStatus (machine à états du cycle de vie)
├── Exceptions/    AppException + EntityNotFound / Validation / Authentication / Authorization
├── Middlewares/   Auth, Role (abstrait) -> Admin, Agent
└── Views/         layouts, auth, reports, dashboard, admin, errors
```

- **Encapsulation** : propriétés `private/protected` typées + accesseurs.
- **Héritage** : `AbstractEntity`, `AbstractRepository`, `AbstractCategory`, `RoleMiddleware`.
- **Polymorphisme** : chaque catégorie redéfinit `processingDays()` / `defaultPriority()`.
- **Classes abstraites** : `AbstractEntity`, `AbstractRepository`, `AbstractCategory`, `AppException`.
- **Interfaces** : `RepositoryInterface`, `NotifiableInterface`.
- **Trait** : `Timestampable`.
- **Exceptions personnalisées** gérées au point d'entrée (`public/index.php`) → pages 403/404/500.
- **Sécurité** : `password_hash`, requêtes préparées PDO, `htmlspecialchars` (`e()`), jeton CSRF,
  protection des routes par middlewares selon le rôle.

## Cycle de vie d'un signalement

`Nouveau → En cours → Résolu` ou `Rejeté` — transitions contrôlées par `ReportStatus`,
chaque changement est historisé (table `status_history`) et notifie l'auteur.