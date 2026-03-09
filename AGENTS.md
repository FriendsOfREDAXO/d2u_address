# D2U Address - Redaxo Addon

A Redaxo 5 CMS addon for managing and displaying addresses, address types, countries, continents, and zip code areas. Includes geocoding support and multiple frontend modules for address output.

## Tech Stack

- **Language:** PHP >= 8.0
- **CMS:** Redaxo >= 5.19.0 (via d2u_helper)
- **Frontend Framework:** Bootstrap 4/5 (via d2u_helper templates)
- **Namespace:** `FriendsOfRedaxo\D2UAddress`

## Project Structure

```text
d2u_address/
├── boot.php               # Addon bootstrap (extension points, permissions)
├── install.php             # Installation (database tables, media manager types, sprog wildcards)
├── update.php              # Update (calls install.php, utf8mb4 conversion)
├── uninstall.php           # Cleanup (database tables, sprog wildcards)
├── package.yml             # Addon configuration, version, dependencies
├── README.md
├── assets/
│   └── noavatar.jpg        # Placeholder image for contacts
├── lang/                   # Backend translations (de_de, en_gb)
├── lib/                    # PHP classes
│   ├── Address.php         # Address model
│   ├── AddressType.php     # Address type model
│   ├── Country.php         # Country model (multilingual)
│   ├── Continent.php       # Continent model (multilingual)
│   ├── ZipCode.php         # Zip code area model
│   ├── LangHelper.php      # Sprog wildcard provider (8 languages)
│   └── Module.php          # Module definitions and revisions
├── modules/                # 3 module variants in group 20
│   └── 20/
│       ├── 1/              # Adressausgabe
│       ├── 2/              # Kontaktbox
│       └── 3/              # Weltkarte
└── pages/                  # Backend pages
    ├── index.php           # Page router
    ├── address.php         # Address management
    ├── address_type.php    # Address type management
    ├── country.php         # Country management
    ├── continent.php       # Continent management
    ├── zip_code.php        # Zip code area management
    ├── settings.settings.php  # Addon settings
    ├── settings.setup.php     # Module manager
    ├── help.readme.php        # Help/README page
    └── help.changelog.php     # Changelog
```

## Coding Conventions

- **Namespace:** `FriendsOfRedaxo\D2UAddress` for all classes
- **Deprecated Namespace:** `D2U_Address` (backward compatibility until 2.0.0)
- **Naming:** camelCase for variables, PascalCase for classes
- **Indentation:** 4 spaces in PHP classes, tabs in module files
- **Comments:** English comments only
- **Frontend labels:** Use `Sprog\Wildcard::get()` backed by `LangHelper`, not `rex_i18n::msg()`
- **Backend labels:** Use `rex_i18n::msg()` with keys from `lang/` files

## AGENTS.md Maintenance

- When new project insights are gained during work and they are relevant to agent guidance, workflows, conventions, architecture, or known pitfalls, update this AGENTS.md accordingly.

## Key Classes

| Class | Description |
| ----- | ----------- |
| `Address` | Address model: company, contact, street, city, geo coordinates, phone, email, picture, address types, priority, online status |
| `AddressType` | Address type model: name, detail view toggle, country select toggle, maps zoom, default address, article link |
| `Country` | Country model (multilingual): name, ISO codes, maps zoom, associated addresses. Implements `ITranslationHelper` |
| `Continent` | Continent model (multilingual): name, associated country IDs. Implements `ITranslationHelper` |
| `ZipCode` | Zip code area model: from-to range, country, associated addresses |
| `LangHelper` | Sprog wildcard provider for 8 languages (DE, EN, FR, ES, NL, RU, PT, ZH) |
| `Module` | Module definitions and revision numbers |

## Database Tables

| Table | Description |
| ----- | ----------- |
| `rex_d2u_address_address` | Addresses: company, contact, street, city, country, coordinates, phone, email, picture, status |
| `rex_d2u_address_types` | Address types: name, display options, maps zoom, default address |
| `rex_d2u_address_countries` | Countries (language-independent): ISO codes, maps zoom |
| `rex_d2u_address_countries_lang` | Countries (language-specific): name, translation status |
| `rex_d2u_address_2_countries` | Many-to-many: addresses ↔ countries |
| `rex_d2u_address_zipcodes` | Zip code areas: range, country, address assignments |
| `rex_d2u_address_continents` | Continents (language-independent): country IDs |
| `rex_d2u_address_continents_lang` | Continents (language-specific): name, translation status |

## Architecture

### Extension Points

| Extension Point | Location | Purpose |
| --------------- | -------- | ------- |
| `CLANG_DELETED` | boot.php (backend) | Cleans up language-specific data when a language is deleted |
| `D2U_HELPER_TRANSLATION_LIST` | boot.php (backend) | Registers addon in D2U Helper translation manager |
| `MEDIA_IS_IN_USE` | boot.php (backend) | Prevents deletion of media files used by addresses |

### Modules

3 module variants in group 20:

| Module | Name | Description |
| ------ | ---- | ----------- |
| 20-1 | D2U Adressen - Adressausgabe | Address output with type filter |
| 20-2 | D2U Adressen - Kontaktbox | Contact box display |
| 20-3 | D2U Adressen - Weltkarte | World map with address markers |

#### Module Versioning

Each module has a revision number defined in `lib/Module.php` inside the `getModules()` method. When a module is changed:

1. Add a changelog entry in `pages/help.changelog.php` describing the change.
2. Increment the module's revision number in `Module::getModules()` by one.

**Important:** The revision only needs to be incremented **once per release**, not per commit. Check the changelog: if the version number is followed by `-DEV`, the release is still in development and no additional revision bump is needed.

### Media Manager Types

| Type | Purpose |
| ---- | ------- |
| `d2u_address_120x150` | Contact preview image (resize 120×150, then crop) |

## Settings

Managed via `pages/settings.settings.php` and stored in `rex_config`:

- `default_country_id` — Default country
- `analytics_emailevent_activate` — Google Analytics email event tracking
- `analytics_emailevent_category` / `analytics_emailevent_action` — Event parameters
- `lang_wildcard_overwrite` — Preserve custom Sprog translations on update
- `lang_replacement_{clang_id}` — Language mapping per REDAXO language

## Dependencies

| Package | Version | Purpose |
| ------- | ------- | ------- |
| `d2u_helper` | >= 1.14.0 | Backend/frontend helpers, module manager, translation interface |
| `sprog` | >= 1.0.0 | Frontend translation wildcards |

## Multi-language Support

- **Backend:** de_de, en_gb
- **Frontend (Sprog Wildcards):** DE, EN, FR, ES, NL, RU, PT, ZH (8 languages)

## Versioning

This addon follows [Semantic Versioning](https://semver.org/):

- **Major** (1st digit): Breaking changes (e.g. removed classes, renamed methods, incompatible DB changes)
- **Minor** (2nd digit): New features, new modules, new database fields (backward compatible)
- **Patch** (3rd digit): Bug fixes, small improvements (backward compatible)

The version number is maintained in `package.yml`. During development, the changelog uses a `-DEV` suffix. The `-DEV` suffix is removed when the version is released.

## Changelog

The changelog is located in `pages/help.changelog.php`.
