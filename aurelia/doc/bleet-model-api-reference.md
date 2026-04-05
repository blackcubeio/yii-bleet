# BleetModel & ElasticModel API Reference

Documentation des classes de modèle formulaire Bleet pour Yii.

## Architecture

```
FormModel (Yiisoft)
    └── BleetModel
            └── ElasticModel
```

---

## BleetModel

Modèle formulaire abstrait avec support scénarios et intégration Elastic optionnelle.

```php
namespace Blackcube\Bleet\Model;

abstract class BleetModel extends FormModel implements RulesProviderInterface
```

### Constantes

```php
BleetModel::ALL_REGULAR_FIELDS   // Inclut toutes les propriétés publiques
BleetModel::NO_REGULAR_FIELDS    // Exclut toutes les propriétés publiques
```

### API Publique

```php
// Form name (support formulaires imbriqués/tabulaires)
->setFormName(string $formName): static
->getFormName(): string

// Scénarios
->setScenario(string $scenario): static
->getScenario(): ?string

// Chargement données
->load(mixed $data, ?string $scope = null): bool
static::loadMultiple(array $models, array $data, ?string $formName = null): bool

// Validation
->validate(): bool
->getRules(): iterable
```

### Méthodes à surcharger

```php
// Définir les scénarios (retourner [] = pas de filtrage)
public function scenarios(): array
{
    return [
        'create' => ['name', 'email', 'password'],
        'update' => ['name', 'email'],
        'admin'  => [self::ALL_REGULAR_FIELDS],
    ];
}

// Définir les règles de validation
protected function rules(): iterable
{
    return [
        'email' => [new Required(), new Email()],
        'name'  => [new Required(), new Length(min: 2, max: 100)],
    ];
}
```

### Comportement scénarios

| Scénario | Comportement |
|----------|--------------|
| `null` (non défini) | Pas de filtrage, tous les champs actifs |
| `[]` (scenarios() vide) | Pas de filtrage, tous les champs actifs |
| Scénario inexistant | `InvalidArgumentException` |
| Scénario défini | Seuls les champs listés sont chargés/validés |

### Exemple usage

```php
class UserForm extends BleetModel
{
    public string $name = '';
    public string $email = '';
    public string $password = '';
    
    public function scenarios(): array
    {
        return [
            'register' => ['name', 'email', 'password'],
            'profile'  => ['name', 'email'],
        ];
    }
    
    protected function rules(): iterable
    {
        return [
            'name'     => [new Required()],
            'email'    => [new Required(), new Email()],
            'password' => [new Required(), new Length(min: 8)],
        ];
    }
}

// Usage
$form = new UserForm();
$form->setScenario('profile');
$form->load($request->getParsedBody());  // password ignoré
$form->validate();                        // password non validé
```

### Formulaires tabulaires

```php
// Données POST : UserForm[0][name], UserForm[1][name], etc.
$models = [new UserForm(), new UserForm()];
BleetModel::loadMultiple($models, $_POST);

// Formulaires imbriqués
$form->setFormName('ParentForm[children][0]');
```

### Intégration Elastic automatique

Si la classe porte l'attribut `#[Elastic]`, les règles JSON Schema sont automatiquement fusionnées avec `rules()`. Les règles PHP ont priorité.

---

## ElasticModel

Extension de BleetModel pour les AR avec ElasticTrait. Gère les propriétés fixes (réflexion) + propriétés élastiques (JSON Schema).

```php
namespace Blackcube\Bleet\Model;

#[Elastic]
abstract class ElasticModel extends BleetModel implements DataSetInterface
```

### Constantes additionnelles

```php
ElasticModel::ALL_ELASTIC_FIELDS   // Inclut toutes les propriétés élastiques
ElasticModel::NO_ELASTIC_FIELDS    // Exclut toutes les propriétés élastiques
```

### API Publique

```php
// Hérité de BleetModel
->setFormName(string): static
->getFormName(): string
->setScenario(string): static
->getScenario(): ?string
->load(mixed $data, ?string $scope = null): bool
->validate(): bool
->getRules(): iterable

// Spécifique ElasticModel
->getSchema(): ?Schema                           // JSON Schema Swaggest
->getElasticValues(): array                      // Valeurs propriétés élastiques
->getElasticPropertyMeta(): array                // Métadonnées champs élastiques
->getPropertyValue(string $property): mixed      // Accès unifié fixe+élastique
->getData(): array                               // Toutes les données (DataSetInterface)
->hasProperty(string $property): bool            // Existence propriété
->populateActiveRecord(object $ar, bool $isNew = false): void

// Magic methods (accès transparent propriétés élastiques)
->__get(string $name): mixed
->__set(string $name, mixed $value): void
->__isset(string $name): bool
```

### Méthodes protégées (pour sous-classes)

```php
// Initialisation depuis AR
protected function initFromAr(object $ar): void
protected function initElasticFromAr(object $ar): void

// Population vers AR
protected function populateToAr(object $ar, bool $isNew = false): void
protected function populateElasticToAr(object $ar): void

// Exclusions
protected static function excludedProperties(): array  // À surcharger si besoin

// Helpers
protected function isElasticProperty(string $name): bool
protected function getActiveFields(): ?array
protected function getRegularFieldNames(): array
protected function getFormProperties(): array
```

### Métadonnées élastiques

`getElasticPropertyMeta()` retourne pour chaque propriété :

```php
[
    'propertyName' => [
        'field'       => 'text|textarea|wysiwyg|email|date|datetime-local|number|checkbox|radio|radiolist|dropdownlist|file|files',
        'label'       => 'Label affiché',
        'fileType'    => 'image|document|...',      // si file/files
        'imageWidth'  => 800,                        // si image
        'imageHeight' => 600,                        // si image
        'options'     => [...],                      // options wysiwyg, etc.
        'items'       => [                           // radiolist/dropdownlist
            ['title' => 'Option 1', 'value' => '1', 'description' => '...'],
        ],
    ],
]
```

### Exemple complet

```php
class ArticleForm extends ElasticModel
{
    public string $title = '';
    public string $slug = '';
    public bool $active = true;
    // + propriétés élastiques depuis JSON Schema AR
    
    protected static function excludedProperties(): array
    {
        return ['id', 'dateCreate', 'dateUpdate'];
    }
    
    public function scenarios(): array
    {
        return [
            'create' => [
                self::ALL_REGULAR_FIELDS,
                self::ALL_ELASTIC_FIELDS,
            ],
            'update' => [
                'title', 'active',
                self::ALL_ELASTIC_FIELDS,
            ],
            'publish' => [
                'active',
                self::NO_ELASTIC_FIELDS,
            ],
        ];
    }
    
    protected function rules(): iterable
    {
        return [
            'title' => [new Required(), new Length(max: 255)],
            'slug'  => [new Required(), new Regex('/^[a-z0-9-]+$/')],
        ];
    }
    
    public static function fromActiveRecord(Article $ar): static
    {
        $form = new static();
        $form->initFromAr($ar);
        return $form;
    }
    
    public function populateActiveRecord(Article $ar, bool $isNew = false): void
    {
        parent::populateActiveRecord($ar, $isNew);
        // Logique supplémentaire si besoin
    }
}

// Usage
$article = Article::findOne($id);
$form = ArticleForm::fromActiveRecord($article);
$form->setScenario('update');

if ($form->load($request->getParsedBody()) && $form->validate()) {
    $form->populateActiveRecord($article);
    $article->save();
}
```

### Accès propriétés élastiques

```php
// Via magic methods (transparent)
$form->elasticPropertyName = 'value';
$value = $form->elasticPropertyName;

// Via getPropertyValue (unifié)
$value = $form->getPropertyValue('anyProperty');

// Via getElasticValues (toutes les élastiques)
$elasticData = $form->getElasticValues();

// Via getData (tout, pour validation)
$allData = $form->getData();
```

### Comportement scénarios ElasticModel

| Constante | Effet |
|-----------|-------|
| `ALL_REGULAR_FIELDS` | Toutes les propriétés publiques PHP |
| `NO_REGULAR_FIELDS` | Aucune propriété publique PHP |
| `ALL_ELASTIC_FIELDS` | Toutes les propriétés JSON Schema |
| `NO_ELASTIC_FIELDS` | Aucune propriété JSON Schema |

Les champs explicites sont auto-détectés (regular vs elastic) :

```php
'scenario' => [
    'title',                    // → regular (propriété PHP)
    'elasticDescription',       // → elastic (propriété JSON Schema)
    self::ALL_ELASTIC_FIELDS,   // → toutes les élastiques
]
```

---

## Normalisation automatique

BleetModel normalise automatiquement les valeurs vides pour les types nullable :

| Type propriété | Valeur `''` | Résultat |
|----------------|-------------|----------|
| `string` | `''` | `''` (inchangé) |
| `?string` | `''` | `''` (inchangé) |
| `?int` | `''` | `null` |
| `?float` | `''` | `null` |
| `?bool` | `''` | `null` |

Évite le cast PHP `'' → 0` pour les entiers nullable.

---

## Fusion règles Elastic

Quand `#[Elastic]` est présent, les règles sont fusionnées :

1. Règles PHP (`rules()`) ont priorité
2. Règles Elastic ajoutées si propriété absente de PHP
3. Pas de doublon de type (ex: pas 2× `Required`)

```php
// PHP rules
protected function rules(): iterable
{
    return [
        'title' => [new Required(), new Length(max: 100)],
    ];
}

// JSON Schema définit : title (required, maxLength: 255), description (required)
// Résultat fusionné :
// - title: Required (PHP), Length(100) (PHP) — Elastic ignoré
// - description: Required (Elastic)
```

---

## Interfaces implémentées

### BleetModel

```php
RulesProviderInterface  // Yiisoft\Validator
```

### ElasticModel

```php
RulesProviderInterface  // Yiisoft\Validator
DataSetInterface        // Yiisoft\Validator (getData(), hasProperty())
```
