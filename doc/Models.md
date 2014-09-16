# Homegrown(MV)C - Models
Homegrown's models are written to be easy to interface with Smarty (each model can be converted
to a hash for use with Smarty's dot notation), although you may use any view engine you want.

## Requirements
HomegrownMVC's models are written to work with PDO, so you can use any database
that has PDO drivers.

## Model types
HomegrownMVC's models are an abstraction of collections and individual elements.
  * PluralModel: Runs queries which return collections of SingularModels
  * SingularModel: Calls operations in PluralModel to instantiate individual
    elements
    
## Creating models
Both PluralModel and SingularModel are abstract classes.

### PluralModel
#### public methods
  * TODO

#### abstract methods
  * TODO

#### overrideable methods
  * TODO


### SingularModel
#### public methods
  * TODO

#### abstract methods
  * TODO

#### overrideable methods
  *TODO

## Interfacing with Smarty templates
PluralModel provides a static `hashify` function which can be used on collections, by calling
each element's specific `hashify` method. Since Smarty can't operate on regular PHP objects,
converting each SingularModel to a hash of data accomplishes this task.
