# Entity-Generator

Entity-Generator - This generator is used to create a standard class stack for a new entity.

### Install

We're highly recommending to install package for only dev environment

```bash
    composer require gilcleis/entity-generator --dev
```


And publish.

```bash
    php artisan vendor:publish --provider="Gilcleis\Support\EntityGeneratorServiceProvider"
```

### Examples
```bash
    php artisan make:entity EntityName \ 
        -S required_string_field \
        --integer=not_required_integer_field \
        --boolean-required=required_boolean_field \
        -j data \
        -e AnotherEntityName
```

### Documentation

`make:entity` artisan command - generate stack of classes to work with the new entity in project.

Syntax: 

```bash
> php artisan make:entity [entity-name] [options]
```

`entity-name` - Name of the Entity, recommended to use `CamelCase` naming style e.g. `WhitelistedDomain`

`options` - one or more options from the lists below

#### Fields definition options

    -i|--integer               : Add integer field to entity.

    -I|--integer-required      : Add required integer field to entity. If you want to specify default value you have to do it manually.

    -f|--float                 : Add float field to entity.

    -F|--float-required        : Add required float field to entity. If you want to specify default value you have to do it manually.

    -s|--string                : Add string field to entity. Default type is VARCHAR(255) but you can change it manually in migration.

    -S|--string-required       : Add required string field to entity. If you want to specify default value ir size you have to do it manually.

    -b|--boolean               : Add boolean field to entity.

    -B|--boolean-required      : Add boolean field to entity. If you want to specify default value you have to do it manually.

    -t|--timestamp             : Add timestamp field to entity.

    -T|--timestamp-required    : Add timestamp field to entity. If you want to specify default value you have to do it manually.

    -j|--json                  : Add json field to entity.

#### Relations definitions options

    -a|--has-one               : Set hasOne relations between you entity and existed entity.

    -A|--has-many              : Set hasMany relations between you entity and existed entity.

    -e|--belongs-to            : Set belongsTo relations between you entity and existed entity.

    -E|--belongs-to-many       : Set belongsToMany relations between you entity and existed entity.   

#### Single class generation mode options

    --only-model               : Set this flag if you want to create only model. This flag is a higher priority than --only-migration, --only-tests and --only-repository.

